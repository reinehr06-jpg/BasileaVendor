<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

define('LARAVEL_START', microtime(true));

// Force HTTPS scheme before anything else (fixes session cookie + CSRF behind reverse proxy)
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['HTTP_X_FORWARDED_PORT'] = 443;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Force HTTPS AFTER bootstrapping (before handling request)
URL::forceScheme('https');

// Override request server params to ensure HTTPS is detected
$request = Request::capture();
$request->server->set('HTTPS', 'on');
$request->server->set('SERVER_PORT', 443);
$request->headers->set('X-Forwarded-Proto', 'https');
$request->headers->set('X-Forwarded-Port', '443');

$app->handleRequest($request);
