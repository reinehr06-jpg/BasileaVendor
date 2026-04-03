<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TwoFactorAuthService
{
    /**
     * Generate a new 2FA secret (base32 encoded)
     */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    /**
     * Generate QR code URL for 2FA setup (uses BaconQrCode library)
     */
    public static function generateQrCode(string $email, string $secret, string $appName = 'BasileiaVendas'): string
    {
        $issuer = urlencode($appName);
        $account = urlencode($email);
        $uri = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&digits=6&period=30";

        try {
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200);
            $svgBackend = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $renderer = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $svgBackend);
            
            $writer = new \BaconQrCode\Writer($renderer);
            $svgContent = $writer->writeString($uri);
            
            return '<img src="data:image/svg+xml;base64,' . base64_encode($svgContent) . '" alt="QR Code 2FA" style="width:200px;height:200px;" />';
        } catch (\Exception $e) {
            try {
                $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200);
                $imagickBackend = new \BaconQrCode\Renderer\Image\ImagickImageBackEnd();
                $renderer = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $imagickBackend);
                
                $writer = new \BaconQrCode\Writer($renderer);
                $pngData = $writer->writeString($uri);
                
                return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" alt="QR Code 2FA" style="width:200px;height:200px;" />';
            } catch (\Exception $e2) {
                Log::error('QR Code generation failed: ' . $e2->getMessage());
                
                return '<img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($uri) . '" alt="QR Code 2FA" style="width:200px;height:200px;" />';
            }
        }
    }

    /**
     * Verify a 2FA token
     */
    public static function verifyToken(string $secret, string $token, int $window = 1): bool
    {
        $token = trim($token);

        if (!preg_match('/^\d{6}$/', $token)) {
            return false;
        }

        if (empty($secret)) {
            return false;
        }

        $secretBinary = self::base32Decode($secret);
        if ($secretBinary === '') {
            return false;
        }

        $time = floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $timeBytes = pack('NN', 0, $time + $offset);
            $hash = hash_hmac('sha1', $timeBytes, $secretBinary, true);

            $offset = ord(substr($hash, -1)) & 0x0F;
            $hashPart = substr($hash, $offset, 4);
            $value = unpack('N', $hashPart)[1];
            $value = $value & 0x7FFFFFFF;
            $calculatedToken = sprintf('%06d', $value % 1000000);

            if (hash_equals($token, $calculatedToken)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate recovery codes
     */
    public static function generateRecoveryCodes(int $count = 10, int $length = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = substr($code, 0, 4) . '-' . substr($code, 4, 4) . '-' . substr($code, 8);
            $codes[] = $code;
        }
        return $codes;
    }

    /**
     * Encode binary data to base32
     */
    private static function base32Encode(string $data): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $result .= $chars[($buffer >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $result .= $chars[($buffer << (5 - $bitsLeft)) & 0x1F];
        }

        return $result;
    }

    /**
     * Decode base32 string to binary
     */
    private static function base32Decode(string $data): string
    {
        $data = strtoupper(str_replace(['=', ' ', '-', '_'], '', $data));
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($chars));

        $buffer = 0;
        $bitsLeft = 0;
        $result = '';

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            if (!isset($map[$data[$i]])) {
                continue;
            }
            $buffer = ($buffer << 5) | $map[$data[$i]];
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $result;
    }
}
