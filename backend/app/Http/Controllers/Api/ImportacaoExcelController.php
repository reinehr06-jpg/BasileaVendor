<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Services\Commission\CommissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportacaoExcelController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function importar(Request $request)
    {
        $request->validate([
            'clientes' => 'required|array',
        ]);

        $clientesData = $request->input('clientes');
        $sucesso = 0;
        $erros = 0;
        $detalhesErros = [];

        DB::beginTransaction();

        try {
            foreach ($clientesData as $index => $row) {
                // Validação básica da linha
                if (empty($row['Nome'])) {
                    continue; // Pula linha vazia
                }

                $nome = $row['Nome'] ?? 'Sem Nome';
                $documento = isset($row['Documento']) ? preg_replace('/\D/', '', $row['Documento']) : null;
                $email = $row['Email'] ?? null;
                $telefone = isset($row['Telefone']) ? preg_replace('/\D/', '', $row['Telefone']) : null;
                $vendedorId = $row['Vendedor_ID'] ?? null;
                $dataVendaRaw = $row['Data_Venda'] ?? null;
                $valor = floatval($row['Valor'] ?? 0);
                $tipoVenda = $row['Tipo_Venda'] ?? 'mensal'; // default to mensal

                // Converter Data (Tenta DD/MM/YYYY ou YYYY-MM-DD ou float do excel)
                $dataVenda = Carbon::now();
                if ($dataVendaRaw) {
                    try {
                        if (is_numeric($dataVendaRaw)) {
                            // Excel serial date format (dias desde 01/01/1900)
                            $dataVenda = Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($dataVendaRaw - 25569) * 86400));
                        } elseif (str_contains($dataVendaRaw, '/')) {
                            $dataVenda = Carbon::createFromFormat('d/m/Y', $dataVendaRaw);
                        } else {
                            $dataVenda = Carbon::parse($dataVendaRaw);
                        }
                    } catch (\Exception $e) {
                        $dataVenda = Carbon::now();
                    }
                }

                // 1) Encontrar ou Criar Cliente
                $cliente = null;
                
                if (!empty($documento)) {
                    $cliente = Cliente::where('documento', $documento)->first();
                }
                
                if (!$cliente && !empty($email)) {
                    $cliente = Cliente::where('email', $email)->first();
                }
                
                if (!$cliente) {
                    $cliente = Cliente::create([
                        'nome' => $nome,
                        'documento' => $documento,
                        'email' => $email,
                        'telefone' => $telefone,
                        'whatsapp' => $telefone,
                        'status' => 'ativo',
                    ]);
                }

                // Verifica se Vendedor Existe
                if ($vendedorId) {
                    $vendedorExists = Vendedor::find($vendedorId);
                    if (!$vendedorExists) {
                        $vendedorId = null;
                    }
                }

                // 2) Criar a Venda
                $venda = Venda::create([
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedorId,
                    'valor' => $valor,
                    'valor_original' => $valor,
                    'valor_final' => $valor,
                    'status' => 'PAGO', // Forçamos PAGO na importação retroativa
                    'tipo_negociacao' => strtolower($tipoVenda),
                    'modo_cobranca' => 'pix', // Default
                    'plano' => 'Importação Manual',
                    'data_venda' => $dataVenda->format('Y-m-d'),
                    'requer_aprovacao' => false,
                    'comissao_gerada' => 0,
                    'parcelas' => 1,
                ]);

                // 3) Criar o Pagamento
                $pagamento = Pagamento::create([
                    'cliente_id' => $cliente->id,
                    'venda_id' => $venda->id,
                    'vendedor_id' => $vendedorId,
                    'valor' => $valor,
                    'forma_pagamento' => 'pix',
                    'status' => 'RECEIVED',
                    'data_vencimento' => $dataVenda->format('Y-m-d'),
                    'data_pagamento' => $dataVenda->format('Y-m-d'),
                ]);

                // Atualizar Cliente
                $cliente->data_ultimo_pagamento = $dataVenda->format('Y-m-d');
                $cliente->save();

                // 4) Gerar Comissões!
                // Utilizamos o motor oficial do sistema
                try {
                    $this->commissionService->processarPagamento($pagamento);
                } catch (\Exception $e) {
                    Log::error("Erro ao processar comissão na importação: " . $e->getMessage());
                    $erros++;
                    $detalhesErros[] = "Linha " . ($index + 2) . ": Falha ao gerar comissão (" . $e->getMessage() . ")";
                    continue;
                }

                $sucesso++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$sucesso registros importados e comissionados com sucesso!",
                'erros' => $erros,
                'detalhes' => $detalhesErros
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro crítico na importação de Excel: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno na importação: ' . $e->getMessage()
            ], 500);
        }
    }
}
