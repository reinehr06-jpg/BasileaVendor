<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    /**
     * Auxiliar para checar se o usuário logado tem permissão para gerenciar a conta alvo
     */
    private function resolveTargetUser(User $currentUser, $targetUserId)
    {
        if (!$targetUserId || $currentUser->id == $targetUserId) {
            return $currentUser;
        }

        // Permite master ou gestor alterar de outros
        if (in_array(strtolower($currentUser->perfil), ['master', 'gestor'])) {
            return User::findOrFail($targetUserId);
        }

        return $currentUser;
    }

    /**
     * Retorna a lista de usuários ativos para o frontend exibir no Select
     */
    public function getUsers(Request $request)
    {
        $user = $request->user();
        if (!in_array(strtolower($user->perfil), ['master', 'gestor'])) {
            return response()->json([
                'success' => true,
                'users' => [
                    ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]
                ]
            ]);
        }

        $users = User::select('id', 'name', 'email', 'perfil')
            ->whereIn('status', ['ativo', '1', 1])
            ->orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Retorna a lista de dispositivos 2FA configurados. 
     * Se for Master/Gestor, retorna de todos os usuários (como no painel original).
     * Se for Vendedor, retorna só os dele.
     */
    public function getDevices(Request $request)
    {
        $currentUser = $request->user();
        $formatted = [];

        if (in_array(strtolower($currentUser->perfil), ['master', 'gestor'])) {
            $users = User::whereNotNull('two_factor_secret')->get();
        } else {
            $users = collect([$currentUser]);
        }

        foreach ($users as $u) {
            $devices = $this->parseTwoFactorDevices($u->two_factor_secret);
            foreach ($devices as $device) {
                $formatted[] = [
                    'dispositivo' => $device['name'],
                    'usuario' => $u->name,
                    'email' => $u->email,
                    'perfil' => strtoupper($u->perfil ?? 'VENDEDOR'),
                    'status' => 'Ativo',
                    'user_id' => $u->id
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'devices' => $formatted
        ]);
    }

    /**
     * Inicia o cadastro de um novo dispositivo (gera QR Code).
     */
    public function generateDevice(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:60',
            'user_id' => 'nullable|integer'
        ]);

        $currentUser = $request->user();
        $targetUser = $this->resolveTargetUser($currentUser, $request->user_id);

        $safeName = trim($request->device_name);
        
        // Verifica limite de 5 dispositivos
        $devices = $this->parseTwoFactorDevices($targetUser->two_factor_secret);
        if (count($devices) >= 5) {
            return response()->json(['success' => false, 'message' => 'Máximo de 5 dispositivos permitidos para este usuário.'], 400);
        }

        // Gera novo segredo
        $newSecret = TwoFactorAuthService::generateSecret();
        $qrCodeHtml = TwoFactorAuthService::generateQrCode($targetUser->email . ' (' . $safeName . ')', $newSecret, config('app.name'));

        return response()->json([
            'success' => true,
            'qr_code_html' => $qrCodeHtml,
            'secret' => $newSecret,
            'device_name' => $safeName,
            'user_id' => $targetUser->id
        ]);
    }

    /**
     * Confirma o token do app e salva o novo dispositivo.
     */
    public function confirmDevice(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:60',
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
            'user_id' => 'nullable|integer'
        ]);

        $currentUser = $request->user();
        $targetUser = $this->resolveTargetUser($currentUser, $request->user_id);

        $secret = $request->secret;
        $code = $request->code;
        $deviceName = trim($request->device_name);

        // Valida o código
        $isValid = TwoFactorAuthService::verifyToken($secret, $code);
        if (!$isValid) {
            return response()->json(['success' => false, 'message' => 'Código inválido. Tente novamente.'], 400);
        }

        // Salvar no BD
        $pairs = [];
        $current = $targetUser->two_factor_secret ?: '';

        if (!empty($current)) {
            foreach (explode(',', $current) as $entry) {
                $entry = trim($entry);
                if ($entry === '') continue;

                if (str_contains($entry, '|')) {
                    $pairs[] = $entry;
                } else {
                    $pairs[] = 'Dispositivo Principal|' . $entry;
                }
            }
        }

        $pairs[] = $deviceName . '|' . $secret;
        
        $targetUser->two_factor_secret = implode(',', $pairs);
        $targetUser->two_factor_enabled = true;
        
        if (empty($targetUser->recovery_codes)) {
            $targetUser->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
        }
        
        $targetUser->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo vinculado com sucesso para ' . $targetUser->name . '!'
        ]);
    }

    /**
     * Remove um dispositivo pelo nome.
     */
    public function removeDevice(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string',
            'user_id' => 'nullable|integer'
        ]);

        $currentUser = $request->user();
        $targetUser = $this->resolveTargetUser($currentUser, $request->user_id);

        $deviceToRemove = trim($request->device_name);

        $pairs = [];
        $current = $targetUser->two_factor_secret ?: '';

        if (!empty($current)) {
            foreach (explode(',', $current) as $entry) {
                $entry = trim($entry);
                if ($entry === '') continue;

                if (str_contains($entry, '|')) {
                    [$name, $secret] = explode('|', $entry, 2);
                    if (trim($name) !== $deviceToRemove) {
                        $pairs[] = $entry;
                    }
                } else {
                    if ($deviceToRemove !== 'Dispositivo Principal') {
                        $pairs[] = 'Dispositivo Principal|' . $entry;
                    }
                }
            }
        }

        if (empty($pairs)) {
            $targetUser->two_factor_secret = null;
            $targetUser->two_factor_enabled = false;
        } else {
            $targetUser->two_factor_secret = implode(',', $pairs);
        }
        
        $targetUser->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo removido.'
        ]);
    }

    /**
     * Auxiliar para ler os dispositivos da string.
     */
    private function parseTwoFactorDevices(?string $raw): array
    {
        if (empty($raw)) return [];

        $devices = [];
        $index = 1;

        foreach (explode(',', $raw) as $entry) {
            $entry = trim($entry);
            if ($entry === '') continue;

            if (str_contains($entry, '|')) {
                [$name, $secret] = explode('|', $entry, 2);
                $name = trim($name) !== '' ? trim($name) : 'Dispositivo ' . $index;
                $secret = trim($secret);
            } else {
                $name = $index === 1 ? 'Dispositivo Principal' : 'Dispositivo ' . $index;
                $secret = trim($entry);
            }

            if ($secret !== '') {
                $devices[] = ['name' => $name, 'secret' => $secret];
                $index++;
            }
        }

        return $devices;
    }
}
