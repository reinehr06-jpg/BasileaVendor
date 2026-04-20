<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\LeadInbound;
use App\Models\LeadSchedule;
use App\Models\LeadField;
use App\Models\LeadFieldValue;
use App\Models\LeadTransferHistory;
use App\Models\QuickReply;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $query = LeadInbound::where('tenant_id', $tenantId);

        if ($perfil === 'vendedor') {
            $vendedor = $user->vendedor;
            if ($vendedor) {
                $query->where('vendedor_id', $vendedor->id);
            }
        } elseif ($perfil === 'gestor') {
            $vendedor = $user->vendedor;
            if ($vendedor && $vendedor->equipe_id) {
                $query->whereHas('vendedor', fn($q) => $q->where('equipe_id', $vendedor->equipe_id));
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('etapa')) {
            $query->where('etapa', $request->etapa);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leads = $query->with(['vendedor.user', 'activeSchedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($leads);
    }

    public function kanban(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $query = LeadInbound::where('tenant_id', $tenantId);

        if ($perfil === 'vendedor') {
            $vendedor = $user->vendedor;
            if ($vendedor) {
                $query->where('vendedor_id', $vendedor->id);
            }
        } elseif ($perfil === 'gestor') {
            $vendedor = $user->vendedor;
            if ($vendedor && $vendedor->equipe_id) {
                $query->whereHas('vendedor', fn($q) => $q->where('equipe_id', $vendedor->equipe_id));
            }
        }

        $leads = $query->select('id', 'name', 'phone', 'email', 'source', 'etapa', 'status', 'vendedor_id', 'created_at')
            ->with(['vendedor.user'])
            ->get()
            ->groupBy('etapa');

        return response()->json([
            'novo' => $leads->get('novo', collect())->values(),
            'contato' => $leads->get('contato', collect())->values(),
            'proposta' => $leads->get('proposta', collect())->values(),
            'ganho' => $leads->get('ganho', collect())->values(),
            'perdido' => $leads->get('perdido', collect())->values(),
        ]);
    }

    public function updateEtapa(Request $request, $id)
    {
        $request->validate([
            'etapa' => 'required|in:novo,contato,proposta,ganho,perdido',
            'motivo_perda' => 'required_if:etapa,perdido',
        ]);

        $user = Auth::user();
        $lead = LeadInbound::findOrFail($id);

        if ($user->perfil === 'vendedor' && $lead->vendedor_id !== $user->vendedor?->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $oldEtapa = $lead->etapa;
        $lead->etapa = $request->etapa;

        if ($request->etapa === 'ganho') {
            $lead->status = 'convertido';
            $lead->converted_at = now();
        } elseif ($request->etapa === 'perdido') {
            $lead->status = 'perdido';
            
            // Se não tem motivo manual, usar IA para classificar
            $motivo = $request->motivo_perda;
            if (!$motivo) {
                $aiResult = $this->classificarMotivoPerdaIA($lead);
                $motivo = $aiResult['motivo'] ?? 'NECESSIDADE';
            }
            $lead->motivo_perda = $motivo;
        } elseif ($request->etapa === 'contato' && !$lead->first_contact_at) {
            $lead->first_contact_at = now();
        }

        $lead->save();

        return response()->json($lead);
    }

    public function transferir(Request $request, $id)
    {
        $request->validate([
            'vendedor_id' => 'required|exists:vendedors,id',
            'motivo' => 'nullable',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        if (!in_array($user->perfil, ['admin', 'gestor'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $lead = LeadInbound::findOrFail($id);
        $oldVendedorId = $lead->vendedor_id;

        $newVendedor = \App\Models\Vendedor::find($request->vendedor_id);
        if (!$newVendedor || $newVendedor->user?->status !== 'active') {
            return response()->json(['error' => 'Vendedor inválido ou inativo'], 400);
        }

        $lead->vendedor_id = $newVendedor->id;
        $lead->save();

        LeadTransferHistory::create([
            'lead_id' => $lead->id,
            'from_vendedor_id' => $oldVendedorId,
            'to_vendedor_id' => $newVendedor->id,
            'tenant_id' => $tenantId,
            'motivo' => $request->motivo,
            'type' => 'manual',
        ]);

        return response()->json($lead);
    }

    public function getTransferHistory($id)
    {
        $lead = LeadInbound::findOrFail($id);
        $history = LeadTransferHistory::where('lead_id', $id)
            ->with(['fromVendedor.user', 'toVendedor.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    public function agendar(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
            'notes' => 'nullable',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $lead = LeadInbound::findOrFail($id);

        if ($user->perfil === 'vendedor' && $lead->vendedor_id !== $user->vendedor?->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $vendedorId = $request->has('vendedor_id') 
            ? $request->vendedor_id 
            : $user->vendedor?->id;

        $schedule = LeadSchedule::create([
            'lead_id' => $lead->id,
            'vendedor_id' => $vendedorId,
            'tenant_id' => $tenantId,
            'scheduled_at' => $request->scheduled_at,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        $lead->agendamento_id = $schedule->id;
        $lead->save();

        return response()->json($schedule, 201);
    }

    public function getAgendamentos(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $vendedorId = $request->has('vendedor_id') 
            ? $request->vendedor_id 
            : $user->vendedor?->id;

        if (!$vendedorId) {
            return response()->json(['error' => 'Vendedor não encontrado'], 400);
        }

        $schedules = LeadSchedule::where('vendedor_id', $vendedorId)
            ->where('tenant_id', $tenantId)
            ->where('is_completed', false)
            ->where('scheduled_at', '>=', now())
            ->with(['lead' => function ($q) {
                $q->select('id', 'name', 'phone', 'email');
            }])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json($schedules);
    }

    public function completarAgendamento($id)
    {
        $schedule = LeadSchedule::findOrFail($id);
        $schedule->complete();

        return response()->json($schedule);
    }

    public function exportar(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        if (!in_array($user->perfil, ['admin', 'gestor'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $query = LeadInbound::where('tenant_id', $tenantId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('etapa')) {
            $query->where('etapa', $request->etapa);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        $leads = $query->with(['vendedor.user'])->get();

        $csv = "ID,Nome,Telefone,Email,Status,Etapa,Origem,Vendedor,Criado em\n";
        
        foreach ($leads as $lead) {
            $csv .= "{$lead->id},\"{$lead->name}\",\"{$lead->phone}\",\"{$lead->email}\",{$lead->status},{$lead->etapa},{$lead->source},\"{$lead->vendedor?->user?->name}\",{$lead->created_at}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_' . date('YmdHis') . '.csv"',
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $query = LeadInbound::where('tenant_id', $tenantId);

        if ($perfil === 'vendedor') {
            $vendedor = $user->vendedor;
            if ($vendedor) {
                $query->where('vendedor_id', $vendedor->id);
            }
        } elseif ($perfil === 'gestor') {
            $vendedor = $user->vendedor;
            if ($vendedor && $vendedor->equipe_id) {
                $query->whereHas('vendedor', fn($q) => $q->where('equipe_id', $vendedor->equipe_id));
            }
        }

        $total = (clone $query)->count();
        
        $porEtapa = [
            'novo' => (clone $query)->where('etapa', 'novo')->count(),
            'contato' => (clone $query)->where('etapa', 'contato')->count(),
            'proposta' => (clone $query)->where('etapa', 'proposta')->count(),
            'ganho' => (clone $query)->where('etapa', 'ganho')->count(),
            'perdido' => (clone $query)->where('etapa', 'perdido')->count(),
        ];

        $porCanal = (clone $query)->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();

        $tempoMedio = (clone $query)
            ->whereNotNull('first_contact_at')
            ->selectRaw('AVG(TIMESTIFFDIFF(first_contact_at, created_at, MINUTE)) as media')
            ->first()
            ->media ?? 0;

        $atrasados = (clone $query)->where('created_at', '<', now()->subHours(48))
            ->whereNotIn('etapa', ['ganho', 'perdido'])
            ->count();

        return response()->json([
            'total' => $total,
            'por_etapa' => $porEtapa,
            'por_canal' => $porCanal,
            'tempo_medio_primeiro_contato_min' => round($tempoMedio),
            'atrasados' => $atrasados,
        ]);
    }

    public function quickReplies(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $vendedorId = $user->vendedor?->id;

        $replies = QuickReply::where('tenant_id', $tenantId)
            ->where(function ($q) use ($vendedorId) {
                $q->where('is_global', true)
                  ->orWhere('vendedor_id', $vendedorId);
            })
            ->orderBy('shortcut')
            ->get();

        return response()->json($replies);
    }

    public function createQuickReply(Request $request)
    {
        $request->validate([
            'shortcut' => 'required|unique:quick_replies,shortcut',
            'content' => 'required',
            'is_global' => 'boolean',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $reply = QuickReply::create([
            'tenant_id' => $tenantId,
            'vendedor_id' => $user->perfil === 'vendedor' ? $user->vendedor?->id : null,
            'shortcut' => $request->shortcut,
            'content' => $request->content,
            'category' => $request->category,
            'is_global' => $request->is_global ?? false,
        ]);

        return response()->json($reply, 201);
    }

    public function deleteQuickReply($id)
    {
        $reply = QuickReply::findOrFail($id);
        $reply->delete();

        return response()->json(['deleted' => true]);
    }

    public function customFields(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $fields = LeadField::where('tenant_id', $tenantId)
            ->orderBy('order')
            ->get();

        return response()->json($fields);
    }

    public function createCustomField(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:lead_fields,name',
            'label' => 'required',
            'type' => 'in:text,select,number,date',
            'options' => 'array',
            'is_required' => 'boolean',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $field = LeadField::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'label' => $request->label,
            'type' => $request->type ?? 'text',
            'options' => $request->options,
            'is_required' => $request->is_required ?? false,
        ]);

        return response()->json($field, 201);
    }

    public function updateLeadCustomFields(Request $request, $id)
    {
        $lead = LeadInbound::findOrFail($id);
        
        foreach ($request->all() as $fieldName => $value) {
            $field = LeadField::where('name', $fieldName)->first();
            if ($field) {
                LeadFieldValue::updateOrCreate(
                    ['lead_id' => $lead->id, 'field_id' => $field->id],
                    ['value' => $value]
                );
            }
        }

        return response()->json($lead->fresh(['fieldValues']));
    }

    private function classificarMotivoPerdaIA(LeadInbound $lead): array
    {
        try {
            $ai = app(AIService::class);
            
            $result = $ai->executar('motivo_perda', [
                'interacoes' => $lead->message ?? '',
                'mensagens' => $lead->message ?? '',
            ], Auth::id());

            if ($result['success']) {
                return ['motivo' => $result['output']];
            }
        } catch (\Exception $e) {
            Log::warning('LeadController: Falha ao classificar motivo de perda via IA', [
                'lead_id' => $lead->id,
                'erro' => $e->getMessage(),
            ]);
        }

        return ['motivo' => 'NECESSIDADE'];
    }
}