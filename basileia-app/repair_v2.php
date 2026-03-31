<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'basileia.vendas@basileia.com';
$password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

$user = User::where('email', $email)->first();

if ($user) {
    $user->password = $password;
    $user->save();
    echo "SUCCESS: User $email password updated to complex version!\n";
} else {
    User::create([
        'name' => 'Administrador Master',
        'email' => $email,
        'password' => $password,
        'perfil' => 'master',
        'status' => 'ativo'
    ]);
    echo "SUCCESS: User $email created with complex version!\n";
}
