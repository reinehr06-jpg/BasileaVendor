<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Services\AsaasService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegracaoEventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::with('creator')
            ->orderByDesc('created_at')
            ->paginate(15);

        $asaasKey = Setting::get('asaas_api_key');
        $config_faltante = empty($asaasKey);

        return view('master.integracoes.eventos', compact('eventos', 'config_faltante'));
    }

    public function store(Request $request, AsaasService $asaas)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'valor' => 'nullable|numeric|min:0',
            'vagas_total' => 'required|integer|min=1|max:10000',
            'whatsapp_vendedor' => 'required|string|max:20',
            'telefone_vendedor' => 'nullable|string|max:20',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'billing_type' => 'required|string|in:BOLETO,CREDIT_CARD,PIX,UNDEFINED',
            'charge_type' => 'required|string|in:DETACHED,RECURRENT,INSTALLMENT',
            'due_date_limit_days' => 'nullable|integer|min:1|max:30',
            'max_allowed_usage' => 'nullable|integer|min:1',
            'max_installments' => 'nullable|integer|min:1|max:12',
            'notification_enabled' => 'nullable|boolean',
            'is_address_required' => 'nullable|boolean',
        ]);

        try {
            // 1. Verificar se tem checkout externo configurado (OBRIGATÓRIO)
            $checkoutBaseUrl = Setting::get('checkout_external_url');
            
            if (!$checkoutBaseUrl) {
                throw new \Exception('Checkout externo não configurado. Acesse Configurações > Integrações e configure a URL do checkout.');
            }

            // 2. Criar registro local primeiro (para gerar ID)
            $slug = $request->slug ?: Str::slug($request->titulo);
            $baseSlug = $slug;
            $i = 1;
            while (Evento::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $evento = Evento::create([
                'slug'                 => $slug,
                'titulo'               => $request->titulo,
                'descricao'            => $request->descricao,
                'valor'                => $request->valor,
                'moeda'                => 'BRL',
                'vagas_total'          => $request->vagas_total,
                'whatsapp_vendedor'    => preg_replace('/\D/', '', $request->whatsapp_vendedor),
                'telefone_vendedor'    => $request->telefone_vendedor ? preg_replace('/\D/', '', $request->telefone_vendedor) : null,
                'data_inicio'          => $request->data_inicio,
                'data_fim'             => $request->data_fim,
                'status'               => 'ativo',
                'checkout_url'         => '',
                'asaas_payment_link_id' => null,
                'billing_type'         => $request->billing_type,
                'charge_type'          => $request->charge_type,
                'due_date_limit_days'  => $request->due_date_limit_days,
                'notification_enabled' => $request->has('notification_enabled'),
                'is_address_required'  => $request->has('is_address_required'),
                'max_allowed_usage'    => (int) $request->vagas_total,
                'end_date'             => $request->data_fim,
                'max_installments'     => (int) ($request->max_installments ?? 1),
                'created_by'           => auth()->id(),
            ]);

            $eventoId = $evento->id;

            // 3. Montar URL de redirect para o checkout próprio
            $redirectUrl = rtrim($checkoutBaseUrl, '/') . '?evento_id=' . $eventoId;

            // 4. Criar Payment Link oficial no Asaas (com redirectUrl)
            $asaasResult = $asaas->createPaymentLink([
                'name'                => $request->titulo,
                'description'         => $request->descricao,
                'billingType'         => $request->billing_type,
                'chargeType'          => $request->charge_type,
                'value'               => $request->valor > 0 ? (float) $request->valor : null,
                'dueDateLimitDays'    => $request->due_date_limit_days,
                'notificationEnabled' => $request->has('notification_enabled'),
                'maxAllowedUsage'     => (int) $request->vagas_total,
                'endDate'             => $request->data_fim ?: null,
                'isAddressRequired'   => $request->has('is_address_required'),
                'maxInstallmentCount' => $request->charge_type === 'INSTALLMENT' ? (int) $request->max_installments : null,
                'redirectUrl'         => $redirectUrl,
            ]);

            $asaasId = $asaasResult['id'] ?? null;
            $asaasUrl = $asaasResult['url'] ?? null;

            if (!$asaasId || !$asaasUrl) {
                throw new \Exception('O Asaas não retornou um ID ou URL válida.');
            }

            // 5. Atualizar evento com URL do payment link do Asaas
            // O checkout próprio será chamado via redirectUrl após o pagamento
            $evento->update([
                'checkout_url' => $asaasUrl,
                'asaas_payment_link_id' => $asaasId,
            ]);

            return back()->with('success', "Link de pagamento criado com sucesso!");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar link de pagamento: ' . $e->getMessage());
            return back()->withInput()->with('error', "Erro ao criar link: " . $e->getMessage());
        }
    }

    public function toggle(\App\Models\Evento $evento)
    {
        $currentStatus = $evento->status ?? 'ativo';
        if ($currentStatus === 'ativo') {
            $evento->update(['status' => 'expirado']);
        } elseif ($currentStatus === 'expirado' && $evento->vagasRestantes() > 0) {
            $evento->update(['status' => 'ativo']);
        }

        return back()->with('success', 'Status do evento atualizado');
    }

    public function destroy(Evento $evento, AsaasService $asaas)
    {
        // 1. Tenta deletar no Asaas primeiro
        if ($evento->asaas_payment_link_id) {
            $success = $asaas->deletePaymentLink($evento->asaas_payment_link_id);
            
            if (!$success) {
                return back()->with('error', 'Falha ao excluir o link no Asaas. A exclusão local foi abortada para evitar que o link continue ativo para clientes.');
            }
        }

        // 2. Deleta localmente
        $evento->delete();
        
        return back()->with('success', 'Evento removido e link arquivado no Asaas com sucesso.');
    }
}
