<?php

namespace App\Services\Chat;

use App\Models\ChatContact;
use App\Models\ChatConversa;
use App\Models\ChatMensagem;
use App\Models\ChatAtividade;
use App\Models\Vendedor;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatDistributionService
{
    protected const MINUTOS_INATIVIDADE = 60;
    protected const MINUTOS_PRIMEIRO_CONTATO = 30;
    protected const LOCK_TIMEOUT = 10;

    public function isEnabled(): bool
    {
        return (bool) Setting::get('chat_enabled', false);
    }

    public function isEnabledForGestor(int $gestorId): bool
    {
        $global = $this->isEnabled();
        if (!$global) {
            return false;
        }

        $gestorConfig = DB::table('chat_gestor_configs')
            ->where('gestor_id', $gestorId)
            ->first();

        if ($gestorConfig) {
            return (bool) $gestorConfig->chat_enabled;
        }

        return $global;
    }

    public function getSlaPrimeiroContato(?int $gestorId = null): int
    {
        if ($gestorId) {
            $gestorConfig = DB::table('chat_gestor_configs')
                ->where('gestor_id', $gestorId)
                ->first();
            if ($gestorConfig && $gestorConfig->sla_primeiro_contato) {
                return (int) $gestorConfig->sla_primeiro_contato;
            }
        }

        return (int) Setting::get('chat_sla_primeiro_contato', self::MINUTOS_PRIMEIRO_CONTATO);
    }

    public function getSlaInatividade(?int $gestorId = null): int
    {
        if ($gestorId) {
            $gestorConfig = DB::table('chat_gestor_configs')
                ->where('gestor_id', $gestorId)
                ->first();
            if ($gestorConfig && $gestorConfig->sla_inatividade) {
                return (int) $gestorConfig->sla_inatividade;
            }
        }

        return (int) Setting::get('chat_sla_inatividade', self::MINUTOS_INATIVIDADE);
    }

    public function getRetornoDias(?int $gestorId = null): int
    {
        if ($gestorId) {
            $gestorConfig = DB::table('chat_gestor_configs')
                ->where('gestor_id', $gestorId)
                ->first();
            if ($gestorConfig && $gestorConfig->retorno_dias) {
                return (int) $gestorConfig->retorno_dias;
            }
        }

        return (int) Setting::get('chat_retorno_dias', 7);
    }

    public function distributeLead(ChatContact $contact): ?ChatConversa
    {
        if (!$this->isEnabled()) {
            Log::info('ChatDistribution: Módulo desativado via feature flag global');
            return null;
        }

        if (!$contact->gestor_id) {
            Log::warning('ChatDistribution: Contato sem gestor_id para distribuição', [
                'contact_id' => $contact->id
            ]);
            return null;
        }

        if (!$this->isEnabledForGestor($contact->gestor_id)) {
            Log::info('ChatDistribution: Módulo desativado para este gestor', [
                'gestor_id' => $contact->gestor_id
            ]);
            return null;
        }

        $lockKey = "chat_distribute_{$contact->gestor_id}";
        
        return DB::transaction(function () use ($contact, $lockKey) {
            $lock = DB::table('chat_distribuicao_fila')
                ->where('gestor_id', $contact->gestor_id)
                ->lockForUpdate()
                ->first();
            
            if (!$lock) {
                $this->initQueueForGestor($contact->gestor_id);
            }
            
            $vendedor = $this->getNextVendedorWithLock($contact->gestor_id);
            
            if (!$vendedor) {
                Log::warning('ChatDistribution: Nenhum vendedor disponível na fila', [
                    'gestor_id' => $contact->gestor_id,
                    'contact_id' => $contact->id
                ]);
                return null;
            }

            $conversa = ChatConversa::create([
                'contact_id' => $contact->id,
                'gestor_id' => $contact->gestor_id,
                'vendedor_id' => $vendedor->id,
                'status' => 'aberta',
                'is_atendido' => false,
                'assigned_at' => now(),
                'last_message_at' => now(),
            ]);

            $this->registrarAtividade($conversa, 'atribuicao_vendedor', 
                "Conversa atribuída automaticamente para {$vendedor->nome}");

            $this->incrementarContadorVendedor($vendedor, $contact->gestor_id);

            Log::info('ChatDistribution: Lead distribuído via Round Robin', [
                'contact_id' => $contact->id,
                'conversa_id' => $conversa->id,
                'vendedor_id' => $vendedor->id,
                'vendedor_nome' => $vendedor->nome
            ]);

            return $conversa;
        });
    }

    protected function getNextVendedorWithLock(int $gestorId): ?Vendedor
    {
        $vendedor = DB::table('chat_distribuicao_fila')
            ->where('gestor_id', $gestorId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->orderBy('ultimo_atendimento_at', 'asc')
            ->orderBy('ordem', 'asc')
            ->first();

        if (!$vendedor) {
            $vendedoresAtivos = Vendedor::where('gestor_id', $gestorId)
                ->where('status', 'ativo')
                ->get();

            if ($vendedoresAtivos->isEmpty()) {
                return null;
            }

            return $vendedoresAtivos->first();
        }

        return Vendedor::find($vendedor->vendedor_id);
    }

    public function getNextVendedor(int $gestorId): ?Vendedor
    {
        $vendedor = DB::table('chat_distribuicao_fila')
            ->where('gestor_id', $gestorId)
            ->where('is_active', true)
            ->orderBy('ultimo_atendimento_at', 'asc')
            ->orderBy('ordem', 'asc')
            ->first();

        if (!$vendedor) {
            $vendedoresAtivos = Vendedor::where('gestor_id', $gestorId)
                ->where('status', 'ativo')
                ->get();

            if ($vendedoresAtivos->isEmpty()) {
                return null;
            }

            $primeiroVendedor = $vendedoresAtivos->first();
            
            foreach ($vendedoresAtivos as $index => $v) {
                DB::table('chat_distribuicao_fila')->updateOrInsert(
                    ['gestor_id' => $gestorId, 'vendedor_id' => $v->id],
                    ['ordem' => $index, 'is_active' => true]
                );
            }

            return $primeiroVendedor;
        }

        return Vendedor::find($vendedor->vendedor_id);
    }

    public function handleInatividade(ChatConversa $conversa): bool
    {
        if ($conversa->status === 'resolvida') {
            Log::info('ChatInatividade: Conversa resolvida, não repassa', ['conversa_id' => $conversa->id]);
            return false;
        }

        if (!$conversa->vendedor_id) {
            Log::info('ChatInatividade: Conversa sem vendedor atribuído', ['conversa_id' => $conversa->id]);
            return false;
        }

        $ultimaMensagem = $conversa->last_inbound_at;
        
        if (!$ultimaMensagem) {
            Log::info('ChatInatividade: Sem mensagem de entrada', ['conversa_id' => $conversa->id]);
            return false;
        }

        if ($conversa->last_outbound_at && $conversa->last_outbound_at > $ultimaMensagem) {
            Log::info('ChatInatividade: Vendedor já respondeu após última mensagem do cliente', ['conversa_id' => $conversa->id]);
            return false;
        }

        $minutosSemResposta = now()->diffInMinutes($ultimaMensagem);
        
        $primeiroContato = $conversa->first_response_at === null;
        $limiteMinutos = $primeiroContato 
            ? $this->getSlaPrimeiroContato($conversa->gestor_id)
            : $this->getSlaInatividade($conversa->gestor_id);
        
        Log::info('ChatInatividade: Verificando', [
            'conversa_id' => $conversa->id,
            'minutos_sem_resposta' => $minutosSemResposta,
            'limite_minutos' => $limiteMinutos,
            'primeiro_contato' => $primeiroContato,
            'first_response_at' => $conversa->first_response_at,
        ]);
        
        if ($minutosSemResposta < $limiteMinutos) {
            return false;
        }

        return $this->transferirPorInatividade($conversa, !$primeiroContato);
    }

    public function transferirPorInatividade(ChatConversa $conversa, bool $jaTeveContato = false): bool
    {
        return DB::transaction(function () use ($conversa, $jaTeveContato) {
            $vendedorAtual = $conversa->vendedor;
            $proximoVendedor = $this->getNextVendedor($conversa->gestor_id);

            if (!$proximoVendedor || $proximoVendedor->id === $conversa->vendedor_id) {
                Log::warning('ChatDistribution: Não há próximo vendedor para transferência por inatividade', [
                    'conversa_id' => $conversa->id,
                    'vendedor_atual' => $conversa->vendedor_id
                ]);
                return false;
            }

            $vendedorAnteriorId = $conversa->vendedor_id;
            
            $updateData = [
                'vendedor_id' => $proximoVendedor->id,
                'assigned_at' => now(),
                'transfer_count' => $conversa->transfer_count + 1,
                'unread_count' => 0,
                'unread_at' => null,
            ];

            $conversa->update($updateData);

            $detalhesTransfer = $jaTeveContato 
                ? "Conversa transferida de {$vendedorAtual->nome} para {$proximoVendedor->nome} por inatividade (>60min após primeira resposta). ATENÇÃO: Cliente já teve contato anterior!"
                : "Conversa transferida de {$vendedorAtual->nome} para {$proximoVendedor->nome} por inatividade (>30min sem primeiro contato)";

            $this->registrarAtividade($conversa, 'inatividade', $detalhesTransfer);

            if ($jaTeveContato) {
                $contact = $conversa->contact;
                $tags = $contact->tags ?? [];
                if (!in_array('repassado_por_inatividade', $tags)) {
                    $tags[] = 'repassado_por_inatividade';
                    $contact->update(['tags' => $tags]);
                }
                
                Log::warning('ChatDistribution: Conversa com cliente que já teve contato anterior foi repassada', [
                    'conversa_id' => $conversa->id,
                    'contact_id' => $contact->id,
                    'de' => $vendedorAnteriorId,
                    'para' => $proximoVendedor->id
                ]);
            }

            $this->decrementarContadorVendedor($vendedorAnteriorId, $conversa->gestor_id);
            $this->incrementarContadorVendedor($proximoVendedor, $conversa->gestor_id);

            Log::info('ChatDistribution: Conversa transferida por inatividade', [
                'conversa_id' => $conversa->id,
                'de' => $vendedorAnteriorId,
                'para' => $proximoVendedor->id,
                'ja_teve_contato' => $jaTeveContato
            ]);

            return true;
        });
    }

    public function redistribuirPorDesativacao(int $vendedorId): array
    {
        $conversas = ChatConversa::where('vendedor_id', $vendedorId)
            ->whereIn('status', ['aberta', 'pendente'])
            ->get();

        $resultados = [
            'total' => $conversas->count(),
            'distribuidas' => 0,
            'falhas' => 0
        ];

        foreach ($conversas as $conversa) {
            $proximoVendedor = $this->getNextVendedor($conversa->gestor_id);
            
            if (!$proximoVendedor || $proximoVendedor->id === $vendedorId) {
                $resultados['falhas']++;
                continue;
            }

            $conversa->update([
                'vendedor_id' => $proximoVendedor->id,
                'assigned_at' => now(),
                'transfer_count' => $conversa->transfer_count + 1,
            ]);

            $this->registrarAtividade($conversa, 'transferencia', 
                "Conversa redistribuída automaticamente após desativação do vendedor");

            $resultados['distribuidas']++;
        }

        DB::table('chat_distribuicao_fila')
            ->where('vendedor_id', $vendedorId)
            ->update(['is_active' => false]);

        Log::info('ChatDistribution: Redistribuição por desativação de vendedor', $resultados);

        return $resultados;
    }

    public function processarNovoLead(array $dadosLead): ?ChatConversa
    {
        if (!$this->isEnabled()) {
            Log::info('ChatDistribution: Módulo desativado, ignorando lead');
            return null;
        }

        $messageId = $dadosLead['message_id'] ?? null;
        $sourceId = $dadosLead['source_id'] ?? null;
        
        if ($messageId) {
            $existingMessage = ChatMensagem::where('external_message_id', $messageId)->first();
            if ($existingMessage) {
                Log::info('ChatDistribution: Mensagem duplicada ignorada (idempotência)', [
                    'external_message_id' => $messageId
                ]);
                return ChatConversa::find($existingMessage->conversa_id);
            }
        }

        if ($sourceId) {
            $existingContact = ChatContact::where('source_id', $sourceId)
                ->where('source', $dadosLead['source'] ?? null)
                ->first();
            if ($existingContact) {
                Log::info('ChatDistribution: Contato já existe via source_id (idempotência)', [
                    'source_id' => $sourceId,
                    'contact_id' => $existingContact->id
                ]);
                return $this->findOrCreateConversa($existingContact, $dadosLead);
            }
        }

        return DB::transaction(function () use ($dadosLead, $messageId) {
            $telefoneNormalizado = ChatContact::normalizePhone($dadosLead['telefone'] ?? '');
            
            $contact = null;
            
            if ($telefoneNormalizado) {
                $contact = ChatContact::where('telefone', $telefoneNormalizado)->first();
            }
            
            if (!$contact && !empty($dadosLead['email'])) {
                $contact = ChatContact::where('email', $dadosLead['email'])->first();
            }

            if (!$contact) {
                $contact = ChatContact::create([
                    'nome' => $dadosLead['nome'] ?? 'Lead',
                    'telefone' => $telefoneNormalizado,
                    'email' => $dadosLead['email'] ?? null,
                    'source' => $dadosLead['source'] ?? 'manual',
                    'source_id' => $sourceId ?? null,
                    'gestor_id' => $dadosLead['gestor_id'] ?? null,
                ]);

                $this->registrarAtividade(null, 'contato_criado', 
                    "Novo contato criado: {$contact->nome} ({$contact->source})");
            }

            return $this->findOrCreateConversa($contact, $dadosLead, $messageId);
        });
    }

    protected function findOrCreateConversa(ChatContact $contact, array $dadosLead, ?string $messageId = null): ?ChatConversa
    {
        $conversa = ChatConversa::where('contact_id', $contact->id)
            ->whereIn('status', ['aberta', 'pendente'])
            ->orderBy('last_message_at', 'desc')
            ->first();

        if (!$conversa) {
            $conversa = $this->distributeLead($contact);
        }

        if ($conversa && !empty($dadosLead['mensagem'])) {
            ChatMensagem::create([
                'conversa_id' => $conversa->id,
                'sender_id' => $contact->id,
                'sender_type' => 'contact',
                'direction' => 'inbound',
                'tipo' => 'texto',
                'conteudo' => $dadosLead['mensagem'],
                'external_message_id' => $messageId,
            ]);

            $conversa->adicionarMensagemEntrada();
            $this->registrarAtividade($conversa, 'mensagem_recebida', 
                "Mensagem recebida do contato");
        }

        return $conversa;
    }

    protected function incrementarContadorVendedor(Vendedor $vendedor, int $gestorId): void
    {
        DB::table('chat_distribuicao_fila')
            ->where('vendedor_id', $vendedor->id)
            ->where('gestor_id', $gestorId)
            ->increment('total_atendidos', 1, [
                'ultimo_atendimento_at' => now()
            ]);
    }

    protected function decrementarContadorVendedor(int $vendedorId, int $gestorId): void
    {
        DB::table('chat_distribuicao_fila')
            ->where('vendedor_id', $vendedorId)
            ->where('gestor_id', $gestorId)
            ->decrement('total_atendidos', 1);
    }

    protected function registrarAtividade(?ChatConversa $conversa, string $acao, string $detalhes): void
    {
        ChatAtividade::create([
            'conversa_id' => $conversa?->id,
            'vendedor_id' => $conversa?->vendedor_id,
            'acao' => $acao,
            'detalhes' => $detalhes
        ]);
    }

    public function initQueueForGestor(int $gestorId): void
    {
        $vendedores = Vendedor::where('gestor_id', $gestorId)
            ->where('status', 'ativo')
            ->get();

        foreach ($vendedores as $index => $vendedor) {
            DB::table('chat_distribuicao_fila')->updateOrInsert(
                ['gestor_id' => $gestorId, 'vendedor_id' => $vendedor->id],
                ['ordem' => $index, 'is_active' => true, 'total_atendidos' => 0]
            );
        }

        Log::info('ChatDistribution: Fila inicializada para gestor', [
            'gestor_id' => $gestorId,
            'vendedores' => $vendedores->count()
        ]);
    }
}