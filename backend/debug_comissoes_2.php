<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rules = \App\Models\CommissionRule::all();
echo "Rules:\n";
foreach($rules as $r) {
    echo "{$r->plan_name}: seller_ini={$r->seller_fixed_value_first_payment}, gestor_ini={$r->manager_fixed_value_first_payment}\n";
}
