<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\Contato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportacaoController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt',
            'campanha_id' => 'nullable|exists:campanhas,id',
        ]);

        $path = $request->file('arquivo')->store('imports');

        $this->processarCSV(storage_path("app/$path"), $request->campanha_id);

        return back()->with('success', 'Importação concluída!');
    }

    private function processarCSV(string $path, ?int $campanhaId): void
    {
        $handle = fopen($path, 'r');
        $cabecalho = fgetcsv($handle, 0, ',');

        $mapeamento = $this->mapearCabecalhos($cabecalho);

        while (($linha = fgetcsv($handle, 0, ',')) !== false) {
            $dados = $this->mapearLinha($linha, $mapeamento);

            Contato::create([
                'nome' => $dados['nome'] ?? 'Sem Nome',
                'email' => $dados['email'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'whatsapp' => $dados['whatsapp'] ?? null,
                'documento' => $dados['documento'] ?? null,
                'campanha_id' => $campanhaId,
                'canal_origem' => 'importacao',
                'status' => 'lead',
                'entry_date' => now(),
                'nome_igreja' => $dados['nome_igreja'] ?? null,
                'nome_pastor' => $dados['nome_pastor'] ?? null,
                'nome_responsavel' => $dados['nome_responsavel'] ?? null,
                'localidade' => $dados['localidade'] ?? null,
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'estado' => $dados['estado'] ?? null,
            ]);
        }

        fclose($handle);
    }

    private function mapearCabecalhos(array $cabecalho): array
    {
        $mapeamento = [];
        foreach ($cabecalho as $indice => $nome) {
            $nomeLower = strtolower(trim($nome));
            $mapeamento[$indice] = match($nomeLower) {
                'nome', 'name', 'cliente', 'lead' => 'nome',
                'email', 'e-mail', 'mail' => 'email',
                'telefone', 'phone', 'fone' => 'telefone',
                'whatsapp', 'wa' => 'whatsapp',
                'documento', 'cpf', 'cnpj' => 'documento',
                'igreja', 'nome_igreja', 'church' => 'nome_igreja',
                'pastor', 'nome_pastor' => 'nome_pastor',
                'responsavel', 'nome_responsavel' => 'nome_responsavel',
                'localidade', 'cidade', 'city' => 'localidade',
                'cep', 'zipcode' => 'cep',
                'endereco', 'address', 'rua' => 'endereco',
                'estado', 'uf' => 'estado',
                default => null,
            };
        }
        return array_filter($mapeamento);
    }

    private function mapearLinha(array $linha, array $mapeamento): array
    {
        $dados = [];
        foreach ($linha as $indice => $valor) {
            if (isset($mapeamento[$indice])) {
                $dados[$mapeamento[$indice]] = trim($valor);
            }
        }
        return $dados;
    }
}