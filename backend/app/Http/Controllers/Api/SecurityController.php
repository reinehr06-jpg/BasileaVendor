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
     * Retorna a lista de dispositivos 2FA configurados pelo usuário.
     */
    public function getDevices(Request $request)
    {
        $user = $request->user();
        $devices = $this->parseTwoFactorDevices($user->two_factor_secret);
        
        // Formatar para a view do frontend
        $formatted = [];
        foreach ($devices as $device) {
            $formatted[] = [
                'dispositivo' => $device['name'],
                'usuario' => $user->name,
                'email' => $user->email,
                'perfil' => strtoupper($user->perfil ?? 'VENDEDOR'),
                'status' => 'Ativo'
            ];
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
            'device_name' => 'required|string|max:60'
        ]);

        $user = $request->user();
        $safeName = trim($request->device_name);
        
        // Verifica limite de 5 dispositivos
        $devices = $this->parseTwoFactorDevices($user->two_factor_secret);
        if (count($devices) >= 5) {
            return response()->json(['success' => false, 'message' => 'Máximo de 5 dispositivos permitidos.'], 400);
        }

        // Gera novo segredo
        $newSecret = TwoFactorAuthService::generateSecret();
        $qrCodeHtml = TwoFactorAuthService::generateQrCode($user->email . ' (' . $safeName . ')', $newSecret, config('app.name'));

        return response()->json([
            'success' => true,
            'qr_code_html' => $qrCodeHtml,
            'secret' => $newSecret,
            'device_name' => $safeName
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
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();
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
        $current = $user->two_factor_secret ?: '';

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
        
        $user->two_factor_secret = implode(',', $pairs);
        $user->two_factor_enabled = true;
        
        if (empty($user->recovery_codes)) {
            $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo vinculado com sucesso!'
        ]);
    }

    /**
     * Remove um dispositivo pelo nome.
     */
    public function removeDevice(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string'
        ]);

        $user = $request->user();
        $deviceToRemove = trim($request->device_name);

        $pairs = [];
        $current = $user->two_factor_secret ?: '';

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
            $user->two_factor_secret = null;
            $user->two_factor_enabled = false;
        } else {
            $user->two_factor_secret = implode(',', $pairs);
        }
        
        $user->save();

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
