<?php

namespace App\Services\Commission;

use App\Models\Comissao;
use App\Models\Pagamento;
use App\Models\Venda;
use App\Models\Vendedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Motor ÚNICO de comissão.
 *
 * Todos os gatilhos (webhook do Asaas, sincronização e o motor noturno)
 * chamam este mesmo serviço, garantindo regra única e sem duplicidade.
 *
 * Idempotência: nunca gera duas comissões para o mesmo pagamento
 * (trava por pagamento_id). Rodar o motor noturno várias vezes é seguro.
 */
class CommissionService
{
    /**
     * Gera (se aplicável) a comissão de vendedor e de gestor para um pagamento
     * confirmado. Retorna um resumo do que aconteceu.
     */
    public static function gerarParaPagamento(Pagamento $pagamento): array
    {
        $resultado = ['gerou' => false, 'motivo' => 'erro_desconhecido'];

        DB::transaction(function () use ($pagamento, &$resultado) {
            $pagamento = Pagamento::where('id', $pagamento->id)->lockForUpdate()->first();

            if (!$pagamento) {
                $resultado = ['gerou' => false, 'motivo' => 'pagamento_nao_encontrado'];
                return;
            }

            $statusPg = strtoupper((string) $pagamento->status);
            if (!in_array($statusPg, ['RECEIVED', 'CONFIRMED', 'PAGO'])) {
                $resultado = ['gerou' => false, 'motivo' => 'pagamento_nao_confirmado'];
                return;
            }
            if (empty($pagamento->data_pagamento)) {
                $resultado = ['gerou' => false, 'motivo' => 'sem_data_pagamento'];
                return;
            }

            if (Comissao::where('pagamento_id', $pagamento->id)->exists()) {
                $resultado = ['gerou' => false, 'motivo' => 'ja_processado'];
                return;
            }

            $venda = $pagamento->venda ?: Venda::find($pagamento->venda_id);
            if (!$venda || !$venda->vendedor_id) {
                $resultado = ['gerou' => false, 'motivo' => 'venda_ou_vendedor_ausente'];
                return;
            }
            $vendedor = Vendedor::find($venda->vendedor_id);
            if (!$vendedor) {
                $resultado = ['gerou' => false, 'motivo' => 'vendedor_nao_encontrado'];
                return;
            }

            $percIni = (float) ($vendedor->comissao_inicial ?? $vendedor->comissao ?? $vendedor->percentual_comissao ?? 0);
            $percRec = (float) ($vendedor->comissao_recorrencia ?? $vendedor->comissao ?? $vendedor->percentual_comissao ?? 0);

            $temGestor = !empty($vendedor->gestor_id) && (int) $vendedor->gestor_id !== (int) $vendedor->usuario_id;

            $percGIni = 0.0;
            $percGRec = 0.0;
            if ($temGestor) {
                $perfilGestor = Vendedor::where('usuario_id', $vendedor->gestor_id)->first();
                $percGIni = (float) ($perfilGestor->comissao_gestor_primeira ?? $vendedor->comissao_gestor_primeira ?? 0);
                $percGRec = (float) ($perfilGestor->comissao_gestor_recorrencia ?? $vendedor->comissao_gestor_recorrencia ?? 0);
            }

            $primeira = Comissao::where('venda_id', $venda->id)->count() === 0;

            $res = CommissionCalculator::calcular([
                'tipo_negociacao'            => $venda->tipo_negociacao ?? 'avulso',
                'parcelas'                   => (int) ($venda->parcelas ?? 1),
                'valor_total'                => (float) ($venda->valor_final ?? $venda->valor ?? 0),
                'pagamento_valor'            => (float) $pagamento->valor,
                'pagamento_data'             => Carbon::parse($pagamento->data_pagamento)->format('Y-m-d'),
                'vencimento_data'            => $pagamento->data_vencimento
                    ? Carbon::parse($pagamento->data_vencimento)->format('Y-m-d')
                    : null,
                'perc_inicial'               => $percIni,
                'perc_recorrencia'           => $percRec,
                'perc_gestor_inicial'        => $percGIni,
                'perc_gestor_recorrencia'    => $percGRec,
                'tem_gestor'                 => $temGestor,
                'primeira_comissao_da_venda' => $primeira,
            ]);

            if (!$res['gerar']) {
                $resultado = ['gerou' => false, 'motivo' => $res['motivo']];
                return;
            }

            $dataPgto = Carbon::parse($pagamento->data_pagamento);
            $competencia = $dataPgto->format('Y-m');
            $criadas = [];

            if ($res['valor_vendedor'] > 0) {
                Comissao::create([
                    'vendedor_id'         => $vendedor->id,
                    'cliente_id'          => $venda->cliente_id,
                    'venda_id'            => $venda->id,
                    'pagamento_id'        => $pagamento->id,
                    'gerente_id'          => null,
                    'competencia'         => $competencia,
                    'tipo_comissao'       => $res['tipo'],
                    'percentual_aplicado' => $res['perc_vendedor'],
                    'percentual_gerente'  => 0,
                    'valor_venda'         => (float) $pagamento->valor,
                    'valor_comissao'      => $res['valor_vendedor'],
                    'valor_gerente'       => 0,
                    'status'              => 'confirmada',
                    'data_pagamento'      => $dataPgto,
                    'eligible_at'         => $dataPgto,
                    'released_at'         => $dataPgto,
                ]);
                $criadas[] = 'vendedor';
            }

            if ($res['valor_gestor'] > 0 && !empty($vendedor->gestor_id)) {
                Comissao::create([
                    'vendedor_id'         => $vendedor->id,
                    'cliente_id'          => $venda->cliente_id,
                    'venda_id'            => $venda->id,
                    'pagamento_id'        => $pagamento->id,
                    'gerente_id'          => $vendedor->gestor_id,
                    'competencia'         => $competencia,
                    'tipo_comissao'       => $res['tipo'],
                    'percentual_aplicado' => 0,
                    'percentual_gerente'  => $res['perc_gestor'],
                    'valor_venda'         => (float) $pagamento->valor,
                    'valor_comissao'      => 0,
                    'valor_gerente'       => $res['valor_gestor'],
                    'status'              => 'confirmada',
                    'data_pagamento'      => $dataPgto,
                    'eligible_at'         => $dataPgto,
                    'released_at'         => $dataPgto,
                ]);
                $criadas[] = 'gestor';
            }

            Log::info('[Comissão] Gerada via motor único', [
                'pagamento_id' => $pagamento->id,
                'venda_id'     => $venda->id,
                'tipo'         => $res['tipo'],
                'vendedor'     => $res['valor_vendedor'],
                'gestor'       => $res['valor_gestor'],
                'criadas'      => $criadas,
            ]);

            if (!empty($criadas)) {
                Cache::tags(["user_{$vendedor->usuario_id}", "user_perfil_vendedor"])->flush();
                if (!empty($vendedor->gestor_id)) {
                    Cache::tags(["user_{$vendedor->gestor_id}", "user_perfil_gestor"])->flush();
                }
            }

            $resultado = [
                'gerou'          => !empty($criadas),
                'motivo'         => empty($criadas) ? 'valores_zerados' : 'ok',
                'tipo'           => $res['tipo'],
                'valor_vendedor' => $res['valor_vendedor'],
                'valor_gestor'   => $res['valor_gestor'],
            ];
        });

        return $resultado;
    }
}
