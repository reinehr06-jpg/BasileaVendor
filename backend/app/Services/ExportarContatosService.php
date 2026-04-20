<?php

namespace App\Services;

use App\Models\Contato;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\CSV;

class ExportarContatosService
{
    public function toCSV(Collection $contatos, string $filename = 'contatos'): string
    {
        $data = $contatos->map(function ($contato) {
            return [
                'ID' => $contato->id,
                'Nome' => $contato->nome,
                'Email' => $contato->email,
                'Telefone' => $contato->telefone,
                'WhatsApp' => $contato->whatsapp,
                'Status' => $contato->status,
                'Campanha' => $contato->campanha?->nome,
                'Canal' => $contato->canal_origem,
                'Score IA' => $contato->ai_score,
                'Entry Date' => $contato->entry_date?->format('d/m/Y H:i'),
                'Criado em' => $contato->created_at->format('d/m/Y H:i'),
            ];
        });

        $path = storage_path("app/exports/{$filename}_" . now()->format('Ymd_His') . '.csv');
        
        CSV::useOutputFile(true);
        CSV::fromArray($data->toArray())->store($path);

        return $path;
    }

    public function toArray(Collection $contatos): array
    {
        return $contatos->map(function ($contato) {
            return [
                'id' => $contato->id,
                'nome' => $contato->nome,
                'email' => $contato->email,
                'telefone' => $contato->telefone,
                'whatsapp' => $contato->whatsapp,
                'documento' => $contato->documento,
                'status' => $contato->status,
                'motivo_perda' => $contato->motivo_perda,
                'campanha_id' => $contato->campanha_id,
                'campanha_nome' => $contato->campanha?->nome,
                'canal_origem' => $contato->canal_origem,
                'utm_source' => $contato->utm_source,
                'utm_medium' => $contato->utm_medium,
                'utm_campaign' => $contato->utm_campaign,
                'entry_date' => $contato->entry_date?->format('Y-m-d H:i:s'),
                'vendedor_id' => $contato->vendedor_id,
                'agente_id' => $contato->agente_id,
                'nome_igreja' => $contato->nome_igreja,
                'nome_pastor' => $contato->nome_pastor,
                'localidade' => $contato->localidade,
                'cidade' => $contato->cidade,
                'estado' => $contato->estado,
                'ai_score' => $contato->ai_score,
                'ai_score_motivo' => $contato->ai_score_motivo,
                'ai_proxima_acao' => $contato->ai_proxima_acao,
                'observacoes' => $contato->observacoes,
                'criado_em' => $contato->created_at->format('Y-m-d H:i:s'),
                'atualizado_em' => $contato->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }
}