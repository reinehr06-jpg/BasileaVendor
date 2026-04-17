<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatContact;
use App\Models\ChatConversa;
use App\Models\ChatMensagem;
use App\Models\ChatAtividade;
use App\Services\Chat\ChatDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatWebhookController extends Controller
{
    protected $distributionService;

    public function __construct()
    {
        $this->distributionService = new ChatDistributionService();
    }

    public function googleAds(Request $request)
    {
        $payload = $request->all();
        
        Log::info('ChatWebhook: Google Ads lead received', $payload);

        try {
            $nome = trim(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''));
            $telefone = $this->normalizePhone($payload['phone'] ?? '');
            $email = $payload['email'] ?? null;
            $sourceId = $payload['lead_id'] ?? $payload['google_id'] ?? null;

            if (empty($telefone) && empty($email)) {
                return response()->json(['error' => 'Telefone ou email obrigatório'], 400);
            }

            $leadData = [
                'nome' => $nome ?: 'Lead Google Ads',
                'telefone' => $telefone,
                'email' => $email,
                'source' => 'google_ads',
                'source_id' => $sourceId,
                'gestor_id' => $this->getGestorFromCampaign($payload['campaign_id'] ?? null),
                'mensagem' => $payload['comment'] ?? null,
                'message_id' => 'google_' . ($payload['lead_id'] ?? time()),
            ];

            $conversa = $this->distributionService->processarNovoLead($leadData);

            if (!$conversa) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead recebido mas módulo de chat está desativado ou sem vendedores disponíveis'
                ]);
            }

            return response()->json([
                'success' => true,
                'contact_id' => $conversa->contact->id ?? null,
                'conversa_id' => $conversa->id ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('ChatWebhook: Erro ao processar lead Google Ads', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function metaLeads(Request $request)
    {
        $payload = $request->all();
        
        Log::info('ChatWebhook: Meta Lead received', $payload);

        try {
            $leadgenId = $payload['leadgen_id'] ?? null;
            $fieldData = $payload['field_data'] ?? [];
            
            $dados = [];
            foreach ($fieldData as $field) {
                $dados[$field['name']] = $field['values'][0] ?? null;
            }

            $nome = $dados['full_name'] ?? $dados['first_name'] ?? 'Lead Meta';
            $telefone = $this->normalizePhone($dados['phone_number'] ?? '');
            $email = $dados['email'] ?? null;

            if (empty($telefone) && empty($email)) {
                return response()->json(['error' => 'Telefone ou email obrigatório'], 400);
            }

            $leadData = [
                'nome' => $nome,
                'telefone' => $telefone,
                'email' => $email,
                'source' => 'meta_leads',
                'source_id' => $leadgenId,
                'gestor_id' => $this->getGestorFromCampaign($payload['campaign_id'] ?? null),
                'mensagem' => null,
                'message_id' => 'meta_' . $leadgenId,
            ];

            $conversa = $this->distributionService->processarNovoLead($leadData);

            if (!$conversa) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead recebido mas módulo de chat está desativado ou sem vendedores disponíveis'
                ]);
            }

            return response()->json([
                'success' => true,
                'contact_id' => $conversa->contact->id ?? null,
                'conversa_id' => $conversa->id ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('ChatWebhook: Erro ao processar lead Meta', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function whatsapp(Request $request)
    {
        $payload = $request->all();
        
        Log::info('ChatWebhook: WhatsApp message received', $payload);

        try {
            $from = $payload['from'] ?? null;
            $messageId = $payload['message_id'] ?? null;
            $type = $payload['type'] ?? 'text';
            
            if (!$from) {
                return response()->json(['error' => 'Número de origem obrigatório'], 400);
            }

            $telefone = $this->normalizePhone($from);
            $conteudo = '';

            if ($messageId) {
                $existingMessage = ChatMensagem::where('external_message_id', $messageId)->first();
                if ($existingMessage) {
                    Log::info('ChatWebhook: Mensagem WhatsApp duplicada ignorada', [
                        'external_message_id' => $messageId
                    ]);
                    return response()->json(['success' => true, 'message' => 'Mensagem duplicada ignorada']);
                }
            }

            switch ($type) {
                case 'text':
                    $conteudo = $payload['text']['body'] ?? '';
                    break;
                case 'image':
                    $conteudo = '[Imagem]';
                    break;
                case 'audio':
                    $conteudo = '[Áudio]';
                    break;
                case 'document':
                    $conteudo = '[Documento]';
                    break;
                case 'video':
                    $conteudo = '[Vídeo]';
                    break;
                default:
                    $conteudo = '[Mensagem]';
            }

            $contact = ChatContact::where('telefone', $telefone)->first();

            if (!$contact) {
                $contact = ChatContact::create([
                    'nome' => 'Cliente WhatsApp',
                    'telefone' => $telefone,
                    'source' => 'whatsapp',
                    'source_id' => $from,
                    'gestor_id' => $this->detectGestorFromPhone($telefone),
                ]);
            }

            $conversa = ChatConversa::where('contact_id', $contact->id)
                ->whereIn('status', ['aberta', 'pendente'])
                ->orderBy('last_message_at', 'desc')
                ->first();

            if (!$conversa) {
                $conversa = $this->distributionService->distributeLead($contact);
            }

            if (!$conversa) {
                return response()->json(['error' => 'Não foi possível criar conversa'], 500);
            }

            $mensagem = ChatMensagem::create([
                'conversa_id' => $conversa->id,
                'sender_id' => $contact->id,
                'sender_type' => 'contact',
                'direction' => 'inbound',
                'tipo' => $this->mapWhatsAppType($type),
                'conteudo' => $conteudo,
                'external_message_id' => $messageId,
                'delivery_status' => 'received'
            ]);

            $conversa->adicionarMensagemEntrada();

            return response()->json([
                'success' => true,
                'message_id' => $mensagem->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ChatWebhook: Erro ao processar mensagem WhatsApp', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function leadInterno(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'telefone' => 'required|string',
            'email' => 'nullable|email',
            'mensagem' => 'nullable|string',
            'gestor_id' => 'nullable|exists:users,id',
            'source' => 'nullable|string',
        ]);

        try {
            $leadData = [
                'nome' => $request->nome,
                'telefone' => $this->normalizePhone($request->telefone),
                'email' => $request->email,
                'source' => $request->source ?? 'landpage',
                'source_id' => null,
                'gestor_id' => $request->gestor_id,
                'mensagem' => $request->mensagem,
                'message_id' => 'lead_' . time(),
            ];

            $conversa = $this->distributionService->processarNovoLead($leadData);

            return response()->json([
                'success' => true,
                'contact_id' => $conversa->contact->id ?? null,
                'conversa_id' => $conversa->id ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('ChatWebhook: Erro ao processar lead interno', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    public function test(Request $request, $webhookId)
    {
        return response()->json([
            'status' => 'ok',
            'webhook_id' => $webhookId,
            'timestamp' => now()->toIso8601String(),
            'message' => 'Webhook funcionando'
        ]);
    }

    protected function normalizePhone($phone)
    {
        if (!$phone) return null;
        
        $clean = preg_replace('/\D/', '', $phone);
        
        if (substr($clean, 0, 2) === '55' && strlen($clean) >= 12) {
            return '+' . $clean;
        }
        
        if (strlen($clean) === 10 || strlen($clean) === 11) {
            return '+55' . $clean;
        }
        
        return '+55' . $clean;
    }

    protected function mapWhatsAppType($type)
    {
        return match($type) {
            'text' => 'texto',
            'image' => 'imagem',
            'audio' => 'audio',
            'document' => 'documento',
            'video' => 'imagem',
            default => 'texto'
        };
    }

    protected function getGestorFromCampaign($campaignId)
    {
        return null;
    }

    protected function detectGestorFromPhone($phone)
    {
        return null;
    }
}