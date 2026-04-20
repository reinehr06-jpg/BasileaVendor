<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportacaoController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'arquivo'     => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'campanha_id' => 'nullable|exists:campanhas,id',
        ]);

        try {
            $path = $request->file('arquivo')->store('imports');
            $extension = $request->file('arquivo')->getClientOriginalExtension();

            if ($extension === 'csv') {
                $count = $this->importarCSV(Storage::path($path), $request->campanha_id);
            } else {
                // Para Excel, seria necessário: composer require maatwebsite/excel
                $count = $this->importarExcel(Storage::path($path), $request->campanha_id);
            }

            // Limpar arquivo após importação
            Storage::delete($path);

            return back()->with('success', "{$count} contatos importados com sucesso!");

        } catch (\Exception $e) {
            \Log::error('Erro na importação', [
                'error' => $e->getMessage(),
                'file' => $request->file('arquivo')->getClientOriginalName()
            ]);

            return back()->with('error', 'Erro ao importar arquivo: ' . $e->getMessage());
        }
    }

    private function importarCSV(string $path, ?int $campanhaId): int
    {
        $csv = \League\Csv\Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $count = 0;
        foreach ($csv->getRecords() as $record) {
            $this->criarContatoDoImport($record, $campanhaId);
            $count++;
        }

        return $count;
    }

    private function importarExcel(string $path, ?int $campanhaId): int
    {
        // Para Excel, usar: composer require maatwebsite/excel
        // Por enquanto, placeholder
        throw new \Exception('Importação Excel ainda não implementada. Use CSV.');
    }

    private function criarContatoDoImport(array $data, ?int $campanhaId): void
    {
        Contato::create([
            'nome'         => $data['nome'] ?? $data['name'] ?? '',
            'email'        => $data['email'] ?? null,
            'telefone'     => $data['telefone'] ?? $data['phone'] ?? null,
            'whatsapp'     => $data['whatsapp'] ?? null,
            'documento'    => $data['documento'] ?? $data['cpf'] ?? null,
            'status'       => 'lead',
            'campanha_id'  => $campanhaId,
            'canal_origem' => 'importacao',
            'entry_date'   => now(),
            // Campos da igreja se existirem
            'nome_igreja'       => $data['nome_igreja'] ?? $data['igreja'] ?? null,
            'nome_pastor'       => $data['nome_pastor'] ?? $data['pastor'] ?? null,
            'nome_responsavel'  => $data['nome_responsavel'] ?? null,
            'quantidade_membros' => $data['quantidade_membros'] ?? $data['membros'] ?? null,
            'cep'               => $data['cep'] ?? null,
            'endereco'          => $data['endereco'] ?? null,
            'cidade'            => $data['cidade'] ?? null,
            'estado'            => $data['estado'] ?? null,
        ]);
    }
}
