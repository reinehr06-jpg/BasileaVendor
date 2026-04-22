<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;

define('LARAVEL_START', microtime(true));

// FIX: Auto generate APP_KEY se não existir - resolve MissingAppKeyException
$envPath = __DIR__ . '/../.env';
$envExamplePath = __DIR__ . '/../.env.example';

// Copia .env.example se .env não existir
if (!file_exists($envPath) && file_exists($envExamplePath)) {
    copy($envExamplePath, $envPath);
}

// Gera APP_KEY automaticamente se não existir
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    if (!preg_match('/^APP_KEY=.{32,}$/m', $envContent)) {
        $key = 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC'));
        
        if (is_writable($envPath)) {
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
            } else {
                $envContent = "APP_KEY={$key}\n" . $envContent;
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        // SOBRESCREVE TODAS AS VARIÁVEIS MESMO SE NÃO CONSEGUIR ESCREVER NO ARQUIVO!
        putenv("APP_KEY={$key}");
        $_ENV['APP_KEY'] = $key;
        $_SERVER['APP_KEY'] = $key;
        
        // Força o Laravel a usar essa chave SEM EXCEÇÃO
        $GLOBALS['APP_KEY'] = $key;
        define('APP_KEY', $key);
    }
}

// FORÇA A CHAVE MESMO SE NÃO TIVER NADA!
if (!isset($_ENV['APP_KEY']) || empty($_ENV['APP_KEY'])) {
    $key = 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC'));
    putenv("APP_KEY={$key}");
    $_ENV['APP_KEY'] = $key;
    $_SERVER['APP_KEY'] = $key;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
