<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Setting;
use Exception;

class HealthController extends Controller
{
    /**
     * Verifica os serviços críticos da aplicação.
     * Retorna HTTP 503 se algum serviço fundamental estiver inoperante.
     */
    public function check()
    {
        $status = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => []
        ];
        
        $hasError = false;

        // 1. Verificando o Banco de Dados (PostgreSQL)
        try {
            DB::connection()->getPdo();
            $status['services']['database'] = 'ok';
        } catch (Exception $e) {
            $status['services']['database'] = 'error';
            $status['details']['database'] = app()->environment('local') ? $e->getMessage() : 'Unavailable';
            $hasError = true;
        }

        // 2. Verificando Redis (opcional - não crítico para operação)
        try {
            if (class_exists('Redis') || config('database.redis.client') === 'predis') {
                Redis::connection()->ping();
                $status['services']['redis'] = 'ok';
            } else {
                $status['services']['redis'] = 'not_available';
                $status['details']['redis'] = 'Redis extension not installed';
            }
        } catch (Exception $e) {
            $status['services']['redis'] = 'warning';
            $status['details']['redis'] = app()->environment('local') ? $e->getMessage() : 'Unavailable';
            // Não marca $hasError = true pois Redis não é crítico para operação básica
        }

        // 3. Verificando Configuração Mínima do Asaas (Opcional para ligar, mas crítico para vendas)
        try {
            // Se o banco estiver fora, isso vai falhar, então encapsulamos em try
            $asaasKey = Setting::get('asaas_api_key');
            if (empty($asaasKey) && app()->environment('production')) {
                $status['services']['asaas'] = 'warning_not_configured';
                // Não marcamos $hasError = true pq a falta da chave não derruba o app inteiro
            } else {
                $status['services']['asaas'] = 'ok';
            }
        } catch (Exception $e) {
            $status['services']['asaas'] = 'unknown';
        }

        if ($hasError) {
            $status['status'] = 'error';
            return response()->json($status, 503);
        }

        return response()->json($status, 200);
    }
}
