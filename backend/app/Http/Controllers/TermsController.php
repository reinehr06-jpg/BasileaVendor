<?php

namespace App\Http\Controllers;

use App\Models\TermsDocument;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class TermsController extends Controller
{
    public function index()
    {
        $termos = TermsDocument::orderByDesc('created_at')->get();
        return view('master.termos.index', compact('termos'));
    }

    public function store(Request $request)
    {
        // AUTO-HEALING: Garantir que a coluna existe no banco caso a migração não tenha rodado
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('terms_documents', 'conteudo_html')) {
                Log::info('TermsController: Auto-healing disparado - adicionando coluna conteudo_html');
                \Illuminate\Support\Facades\Schema::table('terms_documents', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->longText('conteudo_html')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('terms_documents', 'tipo')) {
                \Illuminate\Support\Facades\Schema::table('terms_documents', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('tipo')->nullable();
                });
            }
        } catch (\Exception $e) {
            Log::warning('TermsController: Falha no auto-healing', ['error' => $e->getMessage()]);
        }

        $request->validate([
            'tipo' => 'required|string',
            'titulo' => 'required|string|max:255',
            'versao' => 'required|string|max:20',
            'conteudo_html' => 'nullable', // Permitir nulo pois o arquivo vai preencher
            'arquivo_termo' => 'nullable|file|mimes:pdf,docx,doc,txt|max:5120',
        ]);

        $data = $request->all();

        // Se não tem conteúdo nem arquivo, dá erro
        if (empty($data['conteudo_html']) && !$request->hasFile('arquivo_termo')) {
            return back()->with('error', 'Você precisa fornecer o conteúdo em HTML ou fazer upload de um arquivo (PDF/DOCX/TXT).');
        }

        // Processamento de arquivo
        if ($request->hasFile('arquivo_termo')) {
            try {
                $file = $request->file('arquivo_termo');
                $extension = strtolower($file->getClientOriginalExtension());
                $filePath = $file->getRealPath();
                
                Log::info('TermsController: processando arquivo', ['ext' => $extension]);

                if ($extension === 'txt') {
                    $data['conteudo_html'] = nl2br(e(file_get_contents($filePath)));
                } elseif ($extension === 'pdf') {
                    if (!class_exists('\Smalot\PdfParser\Parser')) {
                        throw new \Exception('Biblioteca de PDF não instalada. Por favor, cole o HTML manualmente.');
                    }
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    $text = $pdf->getText();
                    $data['conteudo_html'] = nl2br(e($text));
                } elseif (in_array($extension, ['docx', 'doc'])) {
                    if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
                        throw new \Exception('Biblioteca de Word não instalada. Por favor, cole o HTML manualmente.');
                    }
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
                    $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
                    
                    $tempFile = tempnam(sys_get_temp_dir(), 'word_html');
                    $htmlWriter->save($tempFile);
                    $htmlContent = file_get_contents($tempFile);
                    @unlink($tempFile);

                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
                        $data['conteudo_html'] = $matches[1];
                    } else {
                        $data['conteudo_html'] = $htmlContent;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao extrair texto do arquivo: ' . $e->getMessage());
                // Se der erro no arquivo, mas ele preencheu o HTML, continua. Senão para.
                if (empty($data['conteudo_html'])) {
                    return back()->withInput()->with('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
                }
            }
        }

        try {
            TermsDocument::create([
                'tipo' => $data['tipo'],
                'titulo' => $data['titulo'],
                'versao' => $data['versao'],
                'conteudo_html' => $data['conteudo_html'] ?? '',
                'ativo' => true
            ]);
        } catch (\Exception $e) {
            Log::error('TermsController: erro ao salvar no banco', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erro ao salvar termo no banco de dados: ' . $e->getMessage());
        }

        return back()->with('success', 'Termo criado com sucesso!');
    }

    public function update(Request $request, TermsDocument $termo)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'versao' => 'required|string|max:20',
            'conteudo_html' => 'required',
        ]);

        $termo->update($request->only(['titulo', 'versao', 'conteudo_html']));

        return back()->with('success', 'Termo atualizado!');
    }

    public function destroy(TermsDocument $termo)
    {
        $termo->delete();
        return back()->with('success', 'Termo removido!');
    }

    public function download(TermsDocument $termo)
    {
        $html = "<html><head><meta charset='UTF-8'><style>body { font-family: sans-serif; line-height: 1.6; padding: 40px; }</style><title>{$termo->titulo}</title></head><body>{$termo->conteudo_html}</body></html>";
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$termo->titulo}-v{$termo->versao}.html\"");
    }

    public function exportPdf(TermsDocument $termo)
    {
        $data = [
            'titulo' => $termo->titulo,
            'versao' => $termo->versao,
            'conteudo' => $termo->conteudo_html,
            'data' => $termo->updated_at->format('d/m/Y')
        ];

        $pdf = Pdf::loadView('pdf.termo', $data);
        
        return $pdf->download("{$termo->titulo}-v{$termo->versao}.pdf");
    }

    public function toggleAtivo(TermsDocument $termo)
    {
        $termo->update(['ativo' => !$termo->ativo]);
        return back()->with('success', $termo->ativo ? 'Termo ativado!' : 'Termo desativado!');
    }
}