<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use BaconQrCode\Renderer\Image\SvgImageRendererBackEnd;
use BaconQrCode\Renderer\ImageImagick;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer\WriterBuilder;

class TwoFactorAuthService
{
    /**
     * Generate a new 2FA secret
     */
    public static function generateSecret(): string
    {
        // Generate a random 16-byte secret and encode it in base32
        $randomBytes = random_bytes(16);
        return base64_encode($randomBytes);
    }

    /**
     * Generate QR code for 2FA setup
     */
    public static function generateQrCode(string $email, string $secret, string $appName = 'BasileiaVendas'): string
    {
        // Generate the TOTP provisioning URI
        $issuer = urlencode($appName);
        $account = urlencode($email);
        $uri = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&digits=6&period=30";

        // Generate QR code
        try {
            $renderer = new SvgImageRendererBackEnd();
            $writer = new Writer($renderer);
            
            $result = $writer->writeString($uri);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to generate 2FA QR code: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Verify a 2FA token
     */
    public static function verifyToken(string $secret, string $token, int $window = 1): bool
    {
        // Remove any whitespace
        $token = trim($token);
        
        // Validate token format (6 digits)
        if (!preg_match('/^\d{6}$/', $token)) {
            return false;
        }
        
        // Validate secret
        if (empty($secret)) {
            return false;
        }
        
        // Calculate time steps
        $time = floor(time() / 30);
        
        // Check current and adjacent time windows
        for ($offset = -$window; $offset <= $window; $offset++) {
            $hash = hash_hmac('sha1', self::intToByetString($time + $offset), $secret, true);
            
            // Truncate hash
            $offset = ord(substr($hash, -1)) & 0x0F;
            $hashPart = substr($hash, $offset, 4);
            
            // Unpack as unsigned long (always 32 bit, big endian)
            $value = unpack('N', $hashPart)[1];
            
            // Apply dynamic truncation
            $value = $value & 0x7FFFFFFF;
            
            // Modulo 10^6 to get 6-digit code
            $calculatedToken = sprintf('%06d', $value % 1000000);
            
            // Constant time comparison to prevent timing attacks
            if (self::hashEquals($token, $calculatedToken)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Convert integer to big-endian byte string
     */
    private static function intToByetString(int $int): string
    {
        // Convert to 64-bit big endian
        return chr(($int >> 56) & 0xFF)
             . chr(($int >> 48) & 0xFF)
             . chr(($int >> 40) & 0xFF)
             . chr(($int >> 32) & 0xFF)
             . chr(($int >> 24) & 0xFF)
             . chr(($int >> 16) & 0xFF)
             . chr(($int >> 8) & 0xFF)
             . chr($int & 0xFF);
    }

    /**
     * Constant time string comparison to prevent timing attacks
     */
    private static function hashEquals(string $known, string $user): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }
        
        $knownLength = strlen($known);
        if ($knownLength !== strlen($user)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < $knownLength; $i++) {
            $result |= ord($known[$i]) ^ ord($user[$i]);
        }
        
        return $result === 0;
    }

    /**
     * Generate recovery codes
     */
    public static function generateRecoveryCodes(int $count = 10, int $length = 10): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                // Generate alphanumeric characters (uppercase letters and numbers)
                $code .= chr(random_int(48, 57)); // 0-9
                if ($j % 2 === 0) { // Alternate between numbers and letters
                    $code[$j] = chr(random_int(65, 90)); // A-Z
                }
            }
            $codes[] = $code;
        }
        
        return $codes;
    }
}