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
                $extension = $file->getClientOriginalExtension();
                $filePath = $file->getRealPath();
                
                if ($extension === 'txt') {
                    $data['conteudo_html'] = nl2br(e(file_get_contents($filePath)));
                } elseif ($extension === 'pdf') {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($filePath);
                    $text = $pdf->getText();
                    $data['conteudo_html'] = nl2br(e($text));
                } elseif (in_array($extension, ['docx', 'doc'])) {
                    $phpWord = IOFactory::load($filePath);
                    $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
                    
                    // Salvar temporariamente o HTML gerado pelo PHPWord
                    $tempFile = tempnam(sys_get_temp_dir(), 'word_html');
                    $htmlWriter->save($tempFile);
                    $htmlContent = file_get_contents($tempFile);
                    unlink($tempFile);

                    // Pegar apenas o corpo do HTML gerado
                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
                        $data['conteudo_html'] = $matches[1];
                    } else {
                        $data['conteudo_html'] = $htmlContent;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao extrair texto do arquivo: ' . $e->getMessage());
                return back()->with('error', 'Erro ao processar o arquivo: ' . $e->getMessage());
            }
        }

        TermsDocument::create($data);

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