<?php

namespace App\Services\AI;

class AIResponseParser
{
    public function scoreLead(string $raw): array
    {
        $raw = trim($raw);

        // Tentar extrair JSON primeiro
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['score'])) {
                return [
                    'score' => (int) ($json['score'] ?? 3),
                    'motivo' => $json['motivo'] ?? 'Avaliação automática',
                ];
            }
        }

        // Fallback: extrair score do texto
        if (preg_match('/(\d)/', $raw, $matches)) {
            $score = (int) $matches[1];
            $score = max(1, min(5, $score));

            // Extrair motivo após os dois pontos
            $motivo = 'Avaliação automática';
            if (preg_match('/原因|porque|motivo[:\s]+(.+)/i', $raw, $m)) {
                $motivo = trim($m[1]);
            }

            return [
                'score' => $score,
                'motivo' => substr($motivo, 0, 200),
            ];
        }

        return ['score' => 3, 'motivo' => 'Avaliação padrão'];
    }

    public function motivoPerda(string $raw): string
    {
        $raw = strtoupper(trim($raw));

        $validos = ['PRECO', 'CONCORRENTE', 'NECESSIDADE', 'TIMING', 'DECISAO', 'QUALIDADE', 'FALTA_CONTATO'];

        foreach ($validos as $valido) {
            if (str_contains($raw, $valido)) {
                return $valido;
            }
        }

        return 'NECESSIDADE'; // padrão
    }

    public function sugestaoResposta(string $raw): array
    {
        $linhas = explode("\n", trim($raw));
        $resultado = [];

        foreach ($linhas as $linha) {
            $limpa = trim(preg_replace('/^\d+[\.\)]\s*/', '', $linha));
            if (mb_strlen($limpa) >= 5 && mb_strlen($limpa) <= 150) {
                $resultado[] = $limpa;
            }
            if (count($resultado) >= 3) break;
        }

        return $resultado ?: ['Resposta 1', 'Resposta 2', 'Resposta 3'];
    }

    public function analiseVendedor(string $raw): array
    {
        $raw = trim($raw);

        $result = [
            'forca' => 'Desempenho consistent',
            'melhoria' => 'Acompanhar métricas',
            'acao' => 'Manter ritmo',
        ];

        if (preg_match('/FORÇA[:\s]*(.+)/i', $raw, $m)) {
            $result['forca'] = trim($m[1]);
        }
        if (preg_match('/MELHORIA[:\s]*(.+)/i', $raw, $m)) {
            $result['melhoria'] = trim($m[1]);
        }
        if (preg_match('/ACAO[:\s]*(.+)/i', $raw, $m)) {
            $result['acao'] = trim($m[1]);
        }

        return $result;
    }

    public function analiseCampanha(string $raw): array
    {
        $raw = trim($raw);

        $result = [
            'avaliacao' => 'Desempenho regular',
            'melhor_canal' => 'A definir',
            'recomendacao' => 'Continuar monitoramento',
        ];

        if (preg_match('/AVALIACAO[:\s]*(.+)/i', $raw, $m)) {
            $result['avaliacao'] = trim($m[1]);
        }
        if (preg_match('/MELHOR_CANAL[:\s]*(.+)/i', $raw, $m)) {
            $result['melhor_canal'] = trim($m[1]);
        }
        if (preg_match('/RECOMENDACAO[:\s]*(.+)/i', $raw, $m)) {
            $result['recomendacao'] = trim($m[1]);
        }

        return $result;
    }

    public function proximaAcao(string $raw): string
    {
        $raw = strtoupper(trim($raw));

        $validos = ['LIGAR_AGORA', 'ENVIAR_WHATSAPP', 'AGENDAR_REUNIAO', 'ENVIAR_PROPOSTA', 'RETORNAR_SEMANA', 'DESCARTAR'];

        foreach ($validos as $valido) {
            if (str_contains($raw, $valido)) {
                return $valido;
            }
        }

        return 'ENVIAR_WHATSAPP'; // padrão
    }

    public function observacaoContato(string $raw): string
    {
        $raw = trim($raw);

        // Remover prefixos comuns
        $raw = preg_replace('/^(OBSERVACAO|OBS:|Observação)[:\s]*/i', '', $raw);

        return mb_substr($raw, 0, 200);
    }

    public function resumoConversa(string $raw): array
    {
        $raw = trim($raw);

        $necessidade = '';
        $proximoPasso = '';

        if (preg_match('/NECESSIDADE[:\s]*(.+)/i', $raw, $m)) {
            $necessidade = trim($m[1]);
        }
        if (preg_match('/PROXIMO_PASSO[:\s]*(.+)/i', $raw, $m)) {
            $proximoPasso = trim($m[1]);
        }

        return [
            'necessidade' => mb_substr($necessidade ?: 'A definir', 0, 50),
            'proximo_passo' => mb_substr($proximoPasso ?: 'A definir', 0, 50),
        ];
    }
}