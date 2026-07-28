<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$value = 'eyJpdiI6IisxNUVhZjlPSFVmejZMK0hHeEJOUFE9PSIsInZhbHVlIjoiSEJDazB3aEQzdGJsbDNmK3A4TTVTOUh5a0IySWZDdHN6TGpGU2dMVWpqK05mRmtBdzgxNlRDanREUWNqSUEwZCIsIm1hYyI6ImQ0NzljZDc2NTAyMzBhYWMwZjg3YTg5NDRkMjUxMDJjNDNmYWI0MDFlMTVhOTc3MDZkZTdmNmUwNWRlMDBhYTYiLCJ0YWciOiIifQ==';
try {
    var_dump(decrypt($value, false));
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
