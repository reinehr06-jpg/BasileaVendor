<?php

namespace App\Services\AI;

class PromptBuilder
{
    public function primeiraMensagem(array $contexto): string
    {
        $nomeNegocio = $contexto['nome_negocio'] ?? 'nossa empresa';
        $produto = $contexto['produto'] ?? 'planos de assinatura';
        $tom = $contexto['tom'] ?? 'amigável e profissional';

        return <<<PROMPT
Você é um especialista em vendas consultivas. Crie 3 primeiras mensagens para um lead novo.

Contexto do negócio: {$nomeNegocio}
Produto/Serviço: {$produto}
Tom de voz: {$tom}

Regras:
- Máximo 150 caracteres por mensagem
- Seja específico, não genérico
- Use {nome} para personalização
- Inclua uma pergunta para engajar o lead
- Gere apenas as 3 mensagens numeradas, sem explicações

Responda no formato:
1. [mensagem 1]
2. [mensagem 2]
3. [mensagem 3]
PROMPT;
    }

    public function sugestaoResposta(array $mensagens): string
    {
        $historico = implode("\n", array_map(fn($m) => $m['texto'] ?? $m, $mensagens));

        return <<<PROMPT
Você é um assistente de vendas. Com base no histórico de conversa abaixo, sugira 3 respostas rápidas para o vendedor enviar.

Histórico:
{$historico}

Regras:
- Máximo 100 caracteres por resposta
- Tom profissional mas amigável
- Seja prático e direto
- Responda apenas com as 3 sugestões numeradas

Responda no formato:
1. [resposta 1]
2. [resposta 2]
3. [resposta 3]
PROMPT;
    }

    public function motivoPerda(array $historico): string
    {
        $interacoes = $historico['interacoes'] ?? '';
        $mensagensPerda = $historico['mensagens'] ?? '';

        return <<<PROMPT
Analise o histórico abaixo e classifique o MOTIVO DA PERDA do lead.

Interações anteriores:
{$interacoes}

Mensagens trocadas:
{$mensagensPerda}

Categorias válidas (escolha apenas uma):
- PRECO: Lead achou caro ou não tem orçamento
- CONCORRENTE: Escolheu outra empresa/concorrente
- NECESSIDADE: Não tem necessidade no momento
- TIMING: Momento não é adequado
- DECISAO: Não conseguiu aprovação/decisão
- QUALIDADE: Duvidas sobre qualidade/serviço
- FALTA_CONTATO: Não conseguiu contato

Responda APENAS com a categoria em letras maiúsculas (ex: PRECO ou CONCORRENTE).
Se não tiver informações suficientes, responda: NECESSIDADE
PROMPT;
    }

    public function scoreLead(array $dadosLead): string
    {
        $nome = $dadosLead['nome'] ?? '';
        $email = $dadosLead['email'] ?? '';
        $telefone = $dadosLead['telefone'] ?? '';
        $church = $dadosLead['church_name'] ?? '';
        $membros = $dadosLead['members_count'] ?? '';
        $source = $dadosLead['source'] ?? '';
        $campanha = $dadosLead['campanha'] ?? '';

        return <<<PROMPT
Avalie a qualidade deste lead de 1 a 5 (sendo 5 muito quente).

Dados do Lead:
- Nome: {$nome}
- Email: {$email}
- Telefone: {$telefone}
- Igreja/Empresa: {$church}
- Quantidade de membros: {$membros}
- Origem: {$source}
- Campanha: {$campanha}

Critérios de avaliação:
- 1 = Frio (sem interesse real)
- 2 = Morno (interesse vago)
- 3 = Morno (interesse confirmado)
- 4 = Quente (interesse real)
- 5 = Muito Quente (próximo a comprar)

Responda no formato JSON:
{"score": NUMERO_1_A_5, "motivo": "原因 resumida em português"}
PROMPT;
    }

    public function analiseVendedor(array $estatisticas): string
    {
        $nome = $estatisticas['nome'] ?? '';
        $mes = $estatisticas['mes'] ?? '';
        $leads = $estatisticas['leads_atendidos'] ?? 0;
        $conversoes = $estatisticas['conversoes'] ?? 0;
        $ticketMedio = $estatisticas['ticket_medio'] ?? 0;
        $tempoMedio = $estatisticas['tempo_medio_resposta'] ?? '';

        return <<<PROMPT
Você é um consultor de vendas. Faça uma análise mensal de desempenho do vendedor {$nome} para o mês de {$mes}.

Estatísticas do período:
- Leads atendidos: {$leads}
- Conversões: {$conversoes}
- Ticket médio: R$ {$ticketMedio}
- Tempo médio de resposta: {$tempoMedio}

Responda:
1. Pontos fortes do vendedor (em 1 frase)
2. Pontos de melhoria (em 1 frase)
3. Recomendação de ação (em 1 frase)

Responda no formato:
FORÇA: [texto]
MELHORIA: [texto]
ACAO: [texto]
PROMPT;
    }

    public function analiseCampanha(array $dados): string
    {
        $nome = $dados['nome'] ?? '';
        $inicio = $dados['data_inicio'] ?? '';
        $fim = $dados['data_fim'] ?? '';
        $leads = $dados['leads_totais'] ?? 0;
        $conversoes = $dados['leads_convertidos'] ?? 0;
        $investimento = $dados['investimento'] ?? 0;
        $canais = implode(', ', $dados['canais'] ?? []);

        return <<<PROMPT
Você é um analista de marketing digital. Avalie o desempenho da campanha "{$nome}".

Período: {$inicio} até {$fm}
Canais: {$canais}
Leads gerados: {$leads}
Conversões: {$conversoes}
Investimento: R$ {$investimento}

Responda:
1. Avaliação geral (em 1 frase)
2. Melhor canal (em 1 frase)
3. Recomendação (em 1 frase)

Responda no formato:
AVALIACAO: [texto]
MELHOR_CANAL: [texto]
RECOMENDACAO: [texto]
PROMPT;
    }

    public function observacaoContato(array $interacoes): string
    {
        $historico = $interacoes['historico'] ?? '';
        $cliente = $interacoes['nome_cliente'] ?? '';
        $ultimoContato = $interacoes['ultimo_contato'] ?? '';

        return <<<PROMPT
Você é um assistente de CRM. Gere uma observação profissional para adicionar ao cadastro do lead/cliente "{$cliente}".

Último contato: {$ultimoContato}
Histórico de interações:
{$historico}

Responda uma OBSERVAÇÃO PROFISSIONAL com no máximo 200 caracteres.
Use linguagem corporativa, seja objetivo e foque em informações relevantes para próximo contato.
PROMPT;
    }

    public function proximaAcao(array $historico): string
    {
        $interacoes = $historico['interacoes'] ?? '';
        $diasSemContato = $historico['dias_sem_contato'] ?? 0;
        $ultimoStatus = $historico['ultimo_status'] ?? '';

        return <<<PROMPT
Analise o histórico abaixo e sugira a PRÓXIMA AÇÃO imediata para este lead.

Último status: {$ultimoStatus}
Dias sem contato: {$diasSemContato}
Histórico:
{$interacoes}

Ações válidas:
- LIGAR_AGORA: Ligar imediatamente
- ENVIAR_WHATSAPP: Enviar mensagem no WhatsApp
- AGENDAR_REUNIAO: Agendar reunião/call
- ENVIAR_PROPOSTA: Enviar proposta comercial
- RETORNAR_SEMANA: Retornar na próxima semana
- DESCARTAR: Descartar lead (sem interesse)

Responda APENAS com a ação recomendada (ex: ENVIAR_WHATSAPP ou LIGAR_AGORA).
PROMPT;
    }

    public function resumoConversa(array $mensagens): string
    {
        $msgs = implode("\n", array_map(fn($m) => ($m['remetente'] ?? '?') . ': ' . ($m['texto'] ?? $m), $mensagens));

        return <<<PROMPT
Resume a conversa abaixo em 2 frases objectives.

Conversa:
{$msgs}

Responda:
1. Resumo do que o lead precisa/interessou (máx 50 caracteres)
2. Próximo passo sugerido (máx 50 caracteres)

Responda no formato:
NECESSIDADE: [texto]
PROXIMO_PASSO: [texto]
PROMPT;
    }
}