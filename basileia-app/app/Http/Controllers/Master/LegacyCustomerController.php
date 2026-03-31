<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Jobs\ImportLegacyCustomerJob;
use App\Jobs\PullAllAsaasCustomersJob;
use App\Models\LegacyCommission;
use App\Models\LegacyCustomerImport;
use App\Models\Plano;
use App\Models\Vendedor;
use App\Services\LegacyCommissionService;
use App\Services\LegacyImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LegacyCustomerController extends Controller
{
    protected LegacyImportService $importService;

    protected LegacyCommissionService $commissionService;

    public function __construct()
    {
        $this->importService = new LegacyImportService;
        $this->commissionService = new LegacyCommissionService;
    }

    public function index(Request $request)
    {
        $query = LegacyCustomerImport::with(['vendedor', 'gestor', 'plano', 'localCliente']);

        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->filled('gestor_id')) {
            $query->where('gestor_id', $request->gestor_id);
        }

        if ($request->filled('import_status')) {
            $query->where('import_status', $request->import_status);
        }

        if ($request->filled('customer_status')) {
            $query->where('customer_status', $request->customer_status);
        }

        if ($request->filled('subscription_status')) {
            $query->where('subscription_status', $request->subscription_status);
        }

        if ($request->filled('sem_vendedor')) {
            $query->whereNull('vendedor_id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('documento', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('asaas_customer_id', 'like', "%{$search}%");
            });
        }

        $imports = $query->orderBy('created_at', 'desc')->paginate(20);

        $vendedores = Vendedor::where('status', 'ativo')
            ->with('user')
            ->get();

        $gestores = Vendedor::where('is_gestor', true)
            ->with('user')
            ->get();

        $stats = [
            'total' => LegacyCustomerImport::count(),
            'imported' => LegacyCustomerImport::where('import_status', 'IMPORTED')->count(),
            'migrated' => LegacyCustomerImport::whereNotNull('local_cliente_id')->count(),
            'pending_customers' => LegacyCustomerImport::where('import_status', 'PENDING')->count(),
            'open_payments' => LegacyCustomerPayment::whereIn('status', ['PENDING', 'OVERDUE'])->count(),
            'active' => LegacyCustomerImport::where('customer_status', 'ACTIVE')->count(),
            'overdue' => LegacyCustomerImport::where('customer_status', 'OVERDUE')->count(),
        ];

        return view('master.legados.index', compact('imports', 'vendedores', 'gestores', 'stats'));
    }

    public function create()
    {
        $vendedores = Vendedor::where('status', 'ativo')
            ->with('user')
            ->get();

        $gestores = Vendedor::where('is_gestor', true)
            ->with('user')
            ->get();

        $planos = Plano::orderBy('nome')->get();

        return view('master.legados.create', compact('vendedores', 'gestores', 'planos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'documento' => 'required|string|max:20',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:20',
            'vendedor_id' => 'required|exists:vendedores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'plano_id' => 'nullable|exists:planos,id',
            'plano_valor_original' => 'nullable|numeric|min:0',
            'plano_valor_recorrente' => 'nullable|numeric|min:0',
            'data_venda_original' => 'nullable|date',
            'generate_old_sale_commission' => 'boolean',
            'generate_recurring_commission' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['imported_by'] = Auth::id();
        $validated['imported_at'] = now();
        $validated['import_status'] = 'PENDING';
        $validated['generate_old_sale_commission'] = $validated['generate_old_sale_commission'] ?? false;
        $validated['generate_recurring_commission'] = $validated['generate_recurring_commission'] ?? true;

        $import = LegacyCustomerImport::create($validated);

        $import->update([
            'import_status' => 'IMPORTED',
            'notes' => 'Cadastro manual criado',
        ]);

        if ($import->hasValidCommercialLink()) {
            $this->importService->generateCommissions($import);
        }

        Log::info('[LegacyCustomer] Cliente legado criado manualmente', [
            'import_id' => $import->id,
            'nome' => $import->nome,
            'vendedor_id' => $import->vendedor_id,
        ]);

        return redirect()->route('master.legados.index')
            ->with('success', 'Cliente legado criado com sucesso!');
    }

    public function show(LegacyCustomerImport $legado)
    {
        $legado->load(['vendedor.usuario', 'gestor', 'plano', 'localCliente', 'payments', 'commissions']);

        return view('master.legados.show', compact('legado'));
    }

    public function update(Request $request, LegacyCustomerImport $legado)
    {
        $validated = $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'plano_id' => 'nullable|exists:planos,id',
            'plano_valor_original' => 'nullable|numeric|min:0',
            'plano_valor_recorrente' => 'nullable|numeric|min:0',
            'data_venda_original' => 'nullable|date',
            'generate_old_sale_commission' => 'boolean',
            'generate_recurring_commission' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['generate_old_sale_commission'] = $validated['generate_old_sale_commission'] ?? false;
        $validated['generate_recurring_commission'] = $validated['generate_recurring_commission'] ?? true;

        $oldVendedorId = $legado->vendedor_id;
        $legado->update($validated);

        // Se o vendedor foi atribuído agora (era nulo) ou alterado, sincronizamos e geramos comissões
        if ($legado->vendedor_id && ($oldVendedorId !== $legado->vendedor_id)) {
            $this->importService->mirrorToLocalTables($legado);
            $this->importService->generateCommissions($legado);
        }

        return redirect()->back()->with('success', 'Cliente legado atualizado e processado!');
    }

    public function importSingle(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'gestor_id' => 'nullable|exists:users,id',
            'plano_id' => 'nullable|exists:planos,id',
        ]);

        $vendedorId = $request->vendedor_id;
        $gestorId = $request->gestor_id;

        $import = LegacyCustomerImport::updateOrCreate(
            ['documento' => preg_replace('/\D/', '', $request->documento)],
            [
                'vendedor_id' => $vendedorId,
                'gestor_id' => $gestorId,
                'plano_id' => $request->plano_id,
                'import_status' => 'PENDING',
            ]
        );

        ImportLegacyCustomerJob::dispatch($import->id);

        return redirect()->back()->with('success', 'Importação iniciada em segundo plano!');
    }

    public function importBatch(Request $request)
    {
        $request->validate([
            'vendedor_id' => 'nullable|exists:vendedores,id',
        ]);

        try {
            $query = \App\Models\Cliente::whereNotNull('documento')
                ->where('documento', '!=', '')
                ->whereHas('vendas');

            if ($request->vendedor_id) {
                $query->whereHas('vendas', function ($q) use ($request) {
                    $q->where('vendedor_id', $request->vendedor_id);
                });
            }

            $clientes = $query->get();
            
            foreach ($clientes as $cliente) {
                $import = LegacyCustomerImport::updateOrCreate(
                    ['documento' => $cliente->documento],
                    [
                        'local_cliente_id' => $cliente->id,
                        'local_cliente_cpf_cnpj' => $cliente->documento,
                        'vendedor_id' => $request->vendedor_id ?: $cliente->vendas->first()?->vendedor_id,
                        'plano_id' => $cliente->vendas->first()?->plano_id,
                        'import_status' => 'PENDING',
                    ]
                );

                ImportLegacyCustomerJob::dispatch($import->id);
            }

            return redirect()->back()->with('success', 'Processamento em lote iniciado para ' . $clientes->count() . ' clientes.');
        } catch (\Exception $e) {
            Log::error('[LegacyCustomer] Erro na importação em lote', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Erro na importação: '.$e->getMessage());
        }
    }

    /**
     * Inicia a descoberta global de clientes no Asaas.
     */
    public function pullAll()
    {
        PullAllAsaasCustomersJob::dispatch();

        return redirect()->back()->with('success', 'Sincronização global com o Asaas iniciada (segundo plano)!');
    }

    public function sync(LegacyCustomerImport $legado)
    {
        try {
            ImportLegacyCustomerJob::dispatch($legado->id);

            return redirect()->back()->with('success', 'Sincronização agendada para segundo plano!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao agendar: '.$e->getMessage());
        }
    }

    public function destroy(LegacyCustomerImport $legado)
    {
        $legado->commissions()->delete();
        $legado->payments()->delete();
        $legado->delete();

        return redirect()->route('master.legados.index')->with('success', 'Cliente legado removido!');
    }

    public function commissions(Request $request)
    {
        $query = LegacyCommission::with(['vendedor.usuario', 'gestor', 'cliente', 'legacyImport']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->filled('gestor_id')) {
            $query->where('gestor_id', $request->gestor_id);
        }

        if ($request->filled('commission_type')) {
            $query->where('commission_type', $request->commission_type);
        }

        if ($request->filled('reference_month')) {
            $query->where('reference_month', $request->reference_month);
        }

        $commissions = $query->orderBy('generated_at', 'desc')->paginate(20);

        $vendedores = Vendedor::where('status', 'ativo')->with('user')->get();
        $gestores = Vendedor::where('is_gestor', true)->with('user')->get();

        $summary = $this->commissionService->getSummary();

        return view('master.legados.commissions', compact('commissions', 'vendedores', 'gestores', 'summary'));
    }

    public function markCommissionPaid(Request $request, LegacyCommission $commission)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->commissionService->markAsPaid($commission->id, $request->notes);

        return redirect()->back()->with('success', 'Comissão marcada como paga!');
    }

    public function markMultiplePaid(Request $request)
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:legacy_commissions,id',
            'notes' => 'nullable|string',
        ]);

        $results = $this->commissionService->markMultipleAsPaid(
            $request->commission_ids,
            $request->notes
        );

        $message = "{$results['success']} comissões marcadas como pagas.";
        if (! empty($results['errors'])) {
            $message .= ' Erros: '.implode(', ', $results['errors']);
        }

        $status = $results['success'] > 0 ? 'success' : 'error';

        return redirect()->back()->with($status, $message);
    }

    public function blockCommission(Request $request, LegacyCommission $commission)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $this->commissionService->blockCommission($commission->id, $request->reason);

        return redirect()->back()->with('success', 'Comissão bloqueada!');
    }

    public function generateRecurring(Request $request)
    {
        try {
            $month = $request->month ?: now()->format('Y-m');
            $stats = $this->commissionService->generateRecurringForAll($request->vendedor_id, $month);

            $message = "Processados: {$stats['processed']} | Geradas: {$stats['generated']} | Puladas: {$stats['skipped']} (Mês: {$month})";

            if (! empty($stats['errors'])) {
                $message .= ' Erros: '.count($stats['errors']);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro: '.$e->getMessage());
        }
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        $header = fgetcsv($handle, 0, ';');
        
        $header = array_map(function($item) {
            return mb_strtolower(trim($item), 'UTF-8');
        }, $header);

        $requiredColumns = ['documento', 'nome'];
        foreach ($requiredColumns as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                return redirect()->back()->with('error', "Coluna obrigatória não encontrada: {$col}");
            }
        }

        $vendedoresCache = Vendedor::with('user')->get()->keyBy(function($v) {
            return mb_strtolower($v->user->name ?? '', 'UTF-8');
        });
        
        $gestoresCache = Vendedor::where('is_gestor', true)->with('user')->get()->keyBy(function($g) {
            return mb_strtolower($g->user->name ?? '', 'UTF-8');
        });
        
        $planosCache = Plano::all()->keyBy(function($p) {
            return mb_strtolower($p->nome, 'UTF-8');
        });

        $rowNumber = 1;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            $data = array_combine($header, $row);
            
            if (! $data) {
                continue;
            }

            try {
                $documento = preg_replace('/\D/', '', $data['documento'] ?? '');
                
                if (empty($documento)) {
                    $errorCount++;
                    $errors[] = "Linha {$rowNumber}: Documento vazio";
                    continue;
                }

                $vendedorId = null;
                $gestorId = null;
                $planoId = null;

                if (! empty($data['vendedor'])) {
                    $vendedorName = mb_strtolower(trim($data['vendedor']), 'UTF-8');
                    $vendedor = $vendedoresCache->get($vendedorName);
                    if ($vendedor) {
                        $vendedorId = $vendedor->id;
                        $gestorId = $vendedor->gestor_id;
                    }
                }

                if (! empty($data['gestor'])) {
                    $gestorName = mb_strtolower(trim($data['gestor']), 'UTF-8');
                    $gestor = $gestoresCache->get($gestorName);
                    if ($gestor) {
                        $gestorId = $gestor->usuario_id;
                    }
                }

                if (! empty($data['plano'])) {
                    $planoName = mb_strtolower(trim($data['plano']), 'UTF-8');
                    $plano = $planosCache->get($planoName);
                    if ($plano) {
                        $planoId = $plano->id;
                    }
                }

                $dataVendaOriginal = null;
                if (! empty($data['data_venda'])) {
                    $dataVendaOriginal = \Carbon\Carbon::createFromFormat('d/m/Y', trim($data['data_venda']))->format('Y-m-d');
                }

                LegacyCustomerImport::create([
                    'local_cliente_cpf_cnpj' => $documento,
                    'nome' => trim($data['nome'] ?? '') ?: 'Sem nome',
                    'documento' => $documento,
                    'email' => trim($data['email'] ?? '') ?: null,
                    'telefone' => trim($data['telefone'] ?? '') ?: null,
                    'vendedor_id' => $vendedorId,
                    'gestor_id' => $gestorId,
                    'plano_id' => $planoId,
                    'plano_valor_original' => floatval(str_replace(',', '.', str_replace('.', '', $data['valor_original'] ?? '0'))),
                    'plano_valor_recorrente' => floatval(str_replace(',', '.', str_replace('.', '', $data['valor_recorrente'] ?? '0'))),
                    'data_venda_original' => $dataVendaOriginal,
                    'customer_status' => 'ACTIVE',
                    'subscription_status' => ! empty($data['recorrente']) && mb_strtolower(trim($data['recorrente']), 'UTF-8') === 'sim' ? 'ACTIVE' : 'NONE',
                    'import_status' => 'IMPORTED',
                    'generate_old_sale_commission' => ! empty($data['gerar_comissao_venda']) && mb_strtolower(trim($data['gerar_comissao_venda']), 'UTF-8') === 'sim',
                    'generate_recurring_commission' => empty($data['gerar_comissao_recorrente']) || mb_strtolower(trim($data['gerar_comissao_recorrente']), 'UTF-8') === 'sim',
                    'imported_by' => Auth::id(),
                    'imported_at' => now(),
                    'notes' => 'Importado via CSV',
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Linha {$rowNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Importação concluída! Sucesso: {$successCount}, Erros: {$errorCount}";
        
        if (! empty($errors) && count($errors) <= 10) {
            $message .= " - " . implode(' | ', array_slice($errors, 0, 10));
        }

        Log::info('[LegacyCustomer] Importação CSV', [
            'success' => $successCount,
            'errors' => $errorCount,
            'user_id' => Auth::id(),
        ]);

        if ($errorCount > 0 && $successCount == 0) {
            return redirect()->back()->with('error', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_clientes_legados.csv"',
        ];

        $columns = [
            'nome',
            'documento',
            'email',
            'telefone',
            'vendedor',
            'gestor',
            'plano',
            'valor_original',
            'valor_recorrente',
            'data_venda',
            'recorrente',
            'gerar_comissao_venda',
            'gerar_comissao_recorrente',
        ];

        $exampleRows = [
            [
                'Igreja Exemplo',
                '12345678901',
                'contato@igreja.com',
                '11999999999',
                'João Silva',
                'Pedro Manager',
                'Plano Básico',
                '297.00',
                '97.00',
                '15/01/2024',
                'sim',
                'não',
                'sim',
            ],
        ];

        $callback = function() use ($columns, $exampleRows) {
            $handle = fopen('php://output', 'w');
            
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, $columns, ';');
            
            foreach ($exampleRows as $row) {
                fputcsv($handle, $row, ';');
            }
            
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
