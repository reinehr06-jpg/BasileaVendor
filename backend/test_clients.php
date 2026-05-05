<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'basileia.vendas@basileia.com')->first();
if (!$user) { echo "User master not found\n"; exit; }
$isMaster = $user->perfil === 'master';

echo "User ID: {$user->id}, Perfil: {$user->perfil}\n";

$query = App\Models\Cliente::query();

if ($isMaster) {
    echo "Query Master\n";
} else {
    $query->whereHas('vendas', function ($q) use ($user) {
        $q->whereHas('vendedor', function ($v) use ($user) {
            $v->where('usuario_id', $user->id)
              ->orWhere('gestor_id', $user->id);
        });
    });
}

echo "Total Clientes Query: " . $query->count() . "\n";
echo "Total Clientes BD: " . App\Models\Cliente::count() . "\n";
echo "Total Legacy Imports Confirmados: " . DB::table('legacy_customer_imports')->whereNotNull('local_cliente_id')->count() . "\n";
