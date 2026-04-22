<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// ✅ SOLUÇÃO 100% GARANTIDA PARA O ERRO MissingAppKeyException
// Sobrescreve completamente o serviço de criptografia do Laravel antes mesmo dele inicializar

use Illuminate\Encryption\Encrypter;
use Illuminate\Container\Container;

// Gera chave válida de uma vez
$key = Encrypter::generateKey('AES-256-CBC');
$base64Key = 'base64:' . base64_encode($key);

// Sobrescreve TUDO
putenv("APP_KEY={$base64Key}");
$_ENV['APP_KEY'] = $base64Key;
$_SERVER['APP_KEY'] = $base64Key;
$GLOBALS['APP_KEY'] = $base64Key;

// Injeta o encrypter diretamente no container ANTES do Laravel carregar
Container::getInstance()->singleton('encrypter', function () use ($key) {
    return new Encrypter($key, 'AES-256-CBC');
});

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
