<?php

namespace App\Helpers;

class DeviceDetection
{
    public static function detectDeviceType(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone|webos|playbook|silk/i', $userAgent)) {
            if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }

        if (preg_match('/bot|crawler|spider|crawl|seo/i', $userAgent)) {
            return 'bot';
        }

        return 'desktop';
    }

    public static function detectBrowser(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/opr\/|opera/i', $userAgent)) {
            return 'Opera';
        }
        if (preg_match('/edg/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edge|opr|edg/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|edg|opr/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/firefox|fxios/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/msie|trident/i', $userAgent)) {
            return 'Internet Explorer';
        }

        return 'Unknown';
    }

    public static function detectOS(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/windows nt 10/i', $userAgent) || preg_match('/windows 10/i', $userAgent)) {
            return 'Windows 10';
        }
        if (preg_match('/windows nt 11/i', $userAgent) || preg_match('/windows 11/i', $userAgent)) {
            return 'Windows 11';
        }
        if (preg_match('/windows nt 6\.3/i', $userAgent) || preg_match('/windows 8\.1/i', $userAgent)) {
            return 'Windows 8.1';
        }
        if (preg_match('/windows nt 6\.2/i', $userAgent) || preg_match('/windows 8/i', $userAgent)) {
            return 'Windows 8';
        }
        if (preg_match('/windows nt 6\.1/i', $userAgent) || preg_match('/windows 7/i', $userAgent)) {
            return 'Windows 7';
        }
        if (preg_match('/windows phone/i', $userAgent)) {
            return 'Windows Phone';
        }
        if (preg_match('/mac os x/i', $userAgent)) {
            if (preg_match('/mac os x 10_15/i', $userAgent) || preg_match('/catalina/i', $userAgent)) {
                return 'macOS Catalina';
            }
            if (preg_match('/mac os x 11/i', $userAgent) || preg_match('/big sur/i', $userAgent)) {
                return 'macOS Big Sur';
            }
            if (preg_match('/mac os x 12/i', $userAgent) || preg_match('/monterey/i', $userAgent)) {
                return 'macOS Monterey';
            }
            if (preg_match('/mac os x 13/i', $userAgent) || preg_match('/ventura/i', $userAgent)) {
                return 'macOS Ventura';
            }
            if (preg_match('/mac os x 14/i', $userAgent) || preg_match('/sonoma/i', $userAgent)) {
                return 'macOS Sonoma';
            }
            if (preg_match('/mac os x 15/i', $userAgent) || preg_match('/sequoia/i', $userAgent)) {
                return 'macOS Sequoia';
            }
            return 'macOS';
        }
        if (preg_match('/iphone os/i', $userAgent) || preg_match('/ios/i', $userAgent) && !preg_match('/android/i', $userAgent)) {
            return 'iOS';
        }
        if (preg_match('/ipad os/i', $userAgent)) {
            return 'iPadOS';
        }
        if (preg_match('/android/i', $userAgent)) {
            if (preg_match('/android 14/i', $userAgent)) {
                return 'Android 14';
            }
            if (preg_match('/android 13/i', $userAgent)) {
                return 'Android 13';
            }
            if (preg_match('/android 12/i', $userAgent)) {
                return 'Android 12';
            }
            if (preg_match('/android 11/i', $userAgent)) {
                return 'Android 11';
            }
            return 'Android';
        }
        if (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/crOS/i', $userAgent)) {
            return 'Chrome OS';
        }

        return 'Unknown';
    }
}

if (!function_exists('detectDeviceType')) {
    function detectDeviceType(?string $userAgent = null): ?string
    {
        return \App\Helpers\DeviceDetection::detectDeviceType($userAgent);
    }
}

if (!function_exists('detectBrowser')) {
    function detectBrowser(?string $userAgent = null): ?string
    {
        return \App\Helpers\DeviceDetection::detectBrowser($userAgent);
    }
}

if (!function_exists('detectOS')) {
    function detectOS(?string $userAgent = null): ?string
    {
        return \App\Helpers\DeviceDetection::detectOS($userAgent);
    }
}
