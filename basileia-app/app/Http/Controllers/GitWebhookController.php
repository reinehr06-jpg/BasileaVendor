<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class GitWebhookController extends Controller
{
    /**
     * Webhook para deploy automático vindo do GitHub.
     * 
     * Rota: POST /webhooks/git-deploy
     */
    public function deploy(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        $secret = config('services.git.deploy_secret');

        if (!$this->verifySignature($signature, $payload, $secret)) {
            Log::warning('Git Deploy: Assinatura inválida', [
                'ip' => $request->ip(),
                'signature' => $signature
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->all();
        $branch = str_replace('refs/heads/', '', $data['ref'] ?? '');

        // Só faz deploy se o push for na branch main
        if ($branch !== 'main') {
            Log::info("Git Deploy: Push na branch [$branch] ignorado.");
            return response()->json(['status' => 'ignored', 'branch' => $branch]);
        }

        Log::info('Git Deploy: Iniciando deploy automático...');

        // Executar comandos de deploy
        // Usamos shell_exec para ambientes onde o Process do Laravel 10+ pode estar limitado
        $commands = [
            'git pull origin main',
            'composer install --no-dev --optimize-autoloader',
            'php artisan migrate --force',
            // 'php artisan db:seed --class=PlanoSeeder --force', // Garante que os novos planos existam
            'php artisan optimize:clear',
            'php artisan optimize',
        ];

        $output = [];
        foreach ($commands as $command) {
            $output[] = "Command: $command";
            $cmdOutput = shell_exec($command . ' 2>&1');
            $output[] = $cmdOutput;
        }

        Log::info('Git Deploy: Finalizado', ['output' => $output]);

        return response()->json([
            'status' => 'success',
            'message' => 'Deploy executado',
            'output' => $output
        ]);
    }

    /**
     * Verifica a assinatura HMAC do GitHub
     */
    protected function verifySignature($signature, $payload, $secret)
    {
        if (empty($secret)) {
            Log::error('Git Deploy: GIT_DEPLOY_SECRET não configurado no .env');
            return false;
        }

        if (empty($signature)) {
            return false;
        }

        $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $hash);
    }
}
