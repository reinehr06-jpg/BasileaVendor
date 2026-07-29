<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\SysadminLog;
use App\Models\SysadminToken;
use App\Services\SecurityAlertService;

class SysadminController extends Controller
{
    /**
     * Emite um token efêmero de acesso ao painel (validade 60 min).
     * Só chega aqui quem já passou por auth:sanctum + master (ver rotas).
     * O token puro é retornado UMA vez; guardamos apenas o hash.
     */
    public function issueToken(Request $request)
    {
        $plain = Str::random(48);

        SysadminToken::create([
            'token_hash' => hash('sha256', $plain),
            'user_id' => $request->user()->id,
            'expires_at' => now()->addMinutes(60),
        ]);

        SecurityAlertService::notify('painel_sysadmin_acessado', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'token' => $plain,
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ]);
    }

    /**
     * Valida o token efêmero enviado no header X-Sysadmin-Token.
     * - Inexistente  -> alerta de segurança + 401
     * - Expirado     -> alerta de segurança + remove + 401
     */
    private function verifyToken(Request $request): void
    {
        $provided = (string) $request->header('X-Sysadmin-Token');
        $record = $provided
            ? SysadminToken::where('token_hash', hash('sha256', $provided))->first()
            : null;

        if (!$record) {
            SecurityAlertService::notify('sysadmin_token_invalido', ['ip' => $request->ip()]);
            abort(401, 'Token inválido');
        }

        if ($record->isExpired()) {
            SecurityAlertService::notify('sysadmin_token_expirado', [
                'ip' => $request->ip(),
                'user_id' => $record->user_id,
            ]);
            $record->delete();
            abort(401, 'Token expirado');
        }

        $record->update(['last_used_at' => now()]);
    }

    public function metrics(Request $request)
    {
        $this->verifyToken($request);

        // Load average (não é %). Normalizamos pelo nº de núcleos para virar % aproximada.
        $cores = (int) (@shell_exec('nproc') ?: 1) ?: 1;
        $load = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0;
        $cpuPercent = round(min(($load / max($cores, 1)) * 100, 100), 2);

        // RAM real do SERVIDOR via /proc/meminfo (Linux). Fallback = null fora de Linux.
        $memTotal = $memUsedPercent = null;
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $t);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $a);
            if (!empty($t[1]) && isset($a[1])) {
                $memTotal = (int) $t[1];              // kB
                $memAvail = (int) $a[1];              // kB
                $memUsedPercent = round((($memTotal - $memAvail) / $memTotal) * 100, 2);
            }
        }

        // Disco
        $diskFree = @disk_free_space('/') ?: 0;
        $diskTotal = @disk_total_space('/') ?: 0;
        $diskPercent = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 2) : 0;

        // Status do banco
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }

        return response()->json([
            'cpu_percent' => $cpuPercent,
            'ram_percent' => $memUsedPercent,
            'ram_total_mb' => $memTotal !== null ? round($memTotal / 1024, 0) : null,
            'disk_percent' => $diskPercent,
            'db_status' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logs(Request $request)
    {
        $this->verifyToken($request);

        $filters = $request->validate([
            'source' => 'nullable|in:frontend,backend,database',
            'level' => 'nullable|in:error,warning,info',
            'search' => 'nullable|string|max:200',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = SysadminLog::query()->orderBy('created_at', 'desc');

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            // payload é json: no Postgres é preciso castar para texto e usar ILIKE.
            $query->where(function ($q) use ($search) {
                $q->where('message', 'ILIKE', $search)
                  ->orWhereRaw('payload::text ILIKE ?', [$search]);
            });
        }
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        $logs = $query->limit($filters['limit'] ?? 500)->get();

        return response()->json(['data' => $logs]);
    }

    public function ingest(Request $request)
    {
        // Sem chave: vem do navegador de qualquer usuário. Protegido por throttle
        // (nas rotas) + validação por enum abaixo. É write-only e descartável.
        $validated = $request->validate([
            'source' => 'required|in:frontend,backend,database',
            'level' => 'required|in:error,warning,info',
            'message' => 'required|string|max:2000',
            'payload' => 'nullable|array',
        ]);

        $log = SysadminLog::create([
            'source' => $validated['source'],
            'level' => $validated['level'],
            'message' => mb_substr($validated['message'], 0, 2000),
            'payload' => $validated['payload'] ?? [],
        ]);

        return response()->json(['success' => true, 'id' => $log->id]);
    }
}
