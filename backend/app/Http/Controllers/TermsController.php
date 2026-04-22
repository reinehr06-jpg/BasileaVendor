<?php

namespace App\Http\Controllers;

use App\Models\TermsDocument;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TermsController extends Controller
{
    public function index()
    {
        $termos = TermsDocument::orderByDesc('created_at')->get();
        return view('master.termos.index', compact('termos'));
    }

    public function store(Request $request)
    {
        Log::info('TermsController: tentativa de salvar', $request->all());

        // AUTO-HEALING: Garantir que a estrutura do banco está correta
        try {
            if (!Schema::hasColumn('terms_documents', 'conteudo_html')) {
                Schema::table('terms_documents', function (Blueprint $table) {
                    $table->longText('conteudo_html')->nullable();
                });
            }
        } catch (\Exception $e) {
            Log::warning('TermsController: Falha no auto-healing', ['error' => $e->getMessage()]);
        }

        $request->validate([
            'tipo' => 'required|string',
            'titulo' => 'required|string|max:255',
            'versao' => 'required|string|max:20',
            'arquivo_termo' => 'nullable|file|mimes:pdf,docx,doc,txt|max:10240',
            'conteudo_html' => 'nullable|string'
        ]);

        $conteudo = $request->conteudo_html;

        // Processamento de arquivo se enviado
        if ($request->hasFile('arquivo_termo')) {
            try {
                $file = $request->file('arquivo_termo');
                $extension = strtolower($file->getClientOriginalExtension());
                $filePath = $file->getRealPath();

                Log::info('TermsController: processando arquivo', ['ext' => $extension]);

                if ($extension === 'pdf') {
                    if (class_exists('\Smalot\PdfParser\Parser')) {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf = $parser->parseFile($filePath);
                        $conteudo = nl2br(e($pdf->getText()));
                    }
                } elseif (in_array($extension, ['docx', 'doc'])) {
                    if (class_exists('\PhpOffice\PhpWord\IOFactory')) {
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
                        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
                        $tempFile = tempnam(sys_get_temp_dir(), 'word_html');
                        $htmlWriter->save($tempFile);
                        $htmlContent = file_get_contents($tempFile);
                        @unlink($tempFile);
                        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
                            $conteudo = $matches[1];
                        } else {
                            $conteudo = $htmlContent;
                        }
                    }
                } elseif ($extension === 'txt') {
                    $conteudo = nl2br(e(file_get_contents($filePath)));
                }
            } catch (\Exception $e) {
                Log::error('TermsController: erro arquivo', ['error' => $e->getMessage()]);
            }
        }

        if (empty($conteudo) && empty($request->conteudo_html)) {
            return back()->withInput()->with('error', 'O conteúdo do termo não pode estar vazio. Certifique-se de preencher o HTML ou subir um arquivo válido.');
        }

        try {
            $finalConteudo = $conteudo ?: $request->conteudo_html;

            $data = [
                'tipo' => $request->tipo,
                'titulo' => $request->titulo,
                'versao' => $request->versao,
                'conteudo_html' => $finalConteudo,
                'ativo' => true,
            ];

            // AUTO-HEALING: Se existir coluna legado 'conteudo', preenchemos ela
            if (Schema::hasColumn('terms_documents', 'conteudo')) {
                $data['conteudo'] = $finalConteudo;
            }

            TermsDocument::create($data);
            return back()->with('success', 'Termo criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('TermsController: erro banco', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erro ao salvar no banco: ' . $e->getMessage());
        }
    }

    public function exportPdf(TermsDocument $termo)
    {
        $data = [
            'termo' => $termo,
            'data' => now()->format('d/m/Y')
        ];

        $pdf = Pdf::loadView('pdf.termo', $data);
        return $pdf->download("termo_{$termo->tipo}_{$termo->versao}.pdf");
    }

    public function destroy(TermsDocument $termo)
    {
        $termo->delete();
        return back()->with('success', 'Termo removido com sucesso!');
    }
}