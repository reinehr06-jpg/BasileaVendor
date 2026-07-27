<?php

$webPhpPath = __DIR__ . '/routes/web.php';
$content = file_get_contents($webPhpPath);

function extractSection(&$content, $startMarker, $endMarker) {
    $posStart = strpos($content, $startMarker);
    if ($posStart === false) return '';
    
    if ($endMarker) {
        $posEnd = strpos($content, $endMarker, $posStart);
        if ($posEnd === false) {
            $posEnd = strlen($content);
        }
    } else {
        $posEnd = strlen($content);
    }
    
    $extracted = substr($content, $posStart, $posEnd - $posStart);
    $content = substr_replace($content, '', $posStart, $posEnd - $posStart);
    return $extracted;
}

$admin = "<?php\n\nuse Illuminate\Support\Facades\Route;\n";
$admin .= "use App\Http\Controllers\MasterPanelController;\nuse App\Http\Controllers\DashboardController;\nuse App\Http\Controllers\EquipeController;\nuse App\Http\Controllers\VendaController;\nuse App\Http\Controllers\PagamentoBoletoController;\nuse App\Http\Controllers\PagamentoController;\nuse App\Http\Controllers\RelatorioController;\nuse App\Http\Controllers\MetaController;\nuse App\Http\Controllers\ClienteController;\nuse App\Http\Controllers\ComissaoController;\nuse App\Http\Controllers\Master\SubscriptionController;\nuse App\Http\Controllers\AprovacaoController;\nuse App\Http\Controllers\NotificacaoController;\nuse App\Http\Controllers\Master\ConfiguracaoController;\nuse App\Http\Controllers\Master\IAController;\nuse App\Http\Controllers\Master\StrictAIController;\nuse App\Http\Controllers\Master\IntegracaoController;\nuse App\Http\Controllers\Master\AsaasClienteSyncController;\nuse App\Http\Controllers\Master\IntegracaoEventoController;\nuse App\Http\Controllers\Master\IntegracaoVendasController;\nuse App\Http\Controllers\ImportacaoController;\nuse App\Http\Controllers\ContatoController;\nuse App\Http\Controllers\TermsController;\nuse App\Http\Controllers\CalendarioController;\nuse App\Http\Controllers\CampanhaController;\n\n";

$admin .= extractSection($content, "// ==========================================\n// IMPORTAÇÃO", "// 2FA Routes");
$admin .= extractSection($content, "// ==========================================\n// TERMOS (Admin)", "// ==========================================\n// TERMOS (Geral)");
$admin .= extractSection($content, "// ==========================================\n    // Módulo Master", "// ==========================================\n    // Módulo Vendedor");

$vendedor = "<?php\n\nuse Illuminate\Support\Facades\Route;\n";
$vendedor .= "use App\Http\Controllers\DashboardController;\nuse App\Http\Controllers\VendaController;\nuse App\Http\Controllers\PagamentoBoletoController;\nuse App\Http\Controllers\PagamentoController;\nuse App\Http\Controllers\ClienteController;\nuse App\Http\Controllers\ComissaoController;\nuse App\Http\Controllers\VendedorSettingsController;\nuse App\Http\Controllers\VendedorConfiguracaoController;\nuse App\Http\Controllers\GestorEquipeController;\nuse App\Http\Controllers\ContatoController;\nuse App\Http\Controllers\CalendarioController;\nuse App\Http\Controllers\PrimeiraMensagemController;\n\n";

$vendedor .= extractSection($content, "// ==========================================\n    // Módulo Vendedor", "    // ==========================================\n    // Módulo Chat - Gestor");

file_put_contents(__DIR__ . '/routes/web/admin.php', $admin);
file_put_contents(__DIR__ . '/routes/web/vendedor.php', $vendedor);
file_put_contents($webPhpPath, $content);
echo "Done script 3\n";
