<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetLegacyCommissions extends Command
{
    protected $signature   = 'legacy:reset-commissions';
    protected $description = 'Zera as comissões de clientes legados no início de cada mês (executar no dia 2).';

    public function handle(): int
    {
        $now = Carbon::now();
        $mesAnterior = $now->copy()->subMonth()->format('Y-m');

        $affected = DB::table('legacy_customer_imports')
            ->whereNotNull('vendedor_id')
            ->where(function($q) use ($mesAnterior) {
                $q->where('comissao_mes_referencia', '=', $mesAnterior)
                  ->orWhereNull('comissao_mes_referencia');
            })
            ->update([
                'comissao_vendedor_calculada' => 0,
                'comissao_gestor_calculada'   => 0,
                'comissao_resetada_em'        => $now,
                'updated_at'                  => $now,
            ]);

        Log::info("ResetLegacyCommissions: {$affected} registros zerados.", [
            'mes_referencia' => $mesAnterior,
            'executado_em'   => $now->toDateTimeString(),
        ]);

        $this->info("✅ {$affected} comissões zeradas para o mês {$mesAnterior}.");

        return self::SUCCESS;
    }
}
