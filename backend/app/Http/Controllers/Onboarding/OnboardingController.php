<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\TermsAcceptance;
use App\Models\TermsDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function verTermos()
    {
        $termo = TermsDocument::ativoPorTipo('uso');
        
        if (!$termo) {
            $termo = TermsDocument::create([
                'tipo' => 'uso',
                'titulo' => 'Termos de Uso',
                'versao' => '1.0.0',
                'conteudo_html' => '<h1>Termos de Uso</h1><p>Bem-vindo ao Basiléia Vendas.</p>',
                'ativo' => true,
            ]);
        }

        return view('onboarding.termos', compact('termo'));
    }

    public function aceitarTermos(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('ACEITAR_TERMOS_ENTRY', $request->all());
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'termos_aceitos' => 'required|accepted',
            'terms_document_id' => 'required|exists:terms_documents,id',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('ACEITAR_TERMOS_VALIDATION_FAILED', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return back()->withErrors($validator)->withInput()->with('error', 'Erro de validação: ' . implode(', ', $validator->errors()->all()));
        }

        try {

            // Registrar o aceite no log
            \App\Models\TermsAcceptance::registrar(
                auth()->id(),
                $request->terms_document_id,
                $request->ip(),
                $request->userAgent()
            );

            // Re-buscar o usuário do banco para garantir que temos a instância correta
            $userId = auth()->id();
            
            // Atualizar usando o Model para disparar eventos e casts corretamente
            $user = \App\Models\User::find($userId);
            $user->termos_aceitos = true;
            $user->save();

            // Sincronizar com o DB para o Middleware ler o valor atualizado
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $userId)
                ->update([
                    'termos_aceitos' => true,
                ]);

            // Limpar caches críticos
            \Illuminate\Support\Facades\Cache::forget('user_permissions_' . $userId);
            \Illuminate\Support\Facades\Session::forget('onboarding_pending_' . $userId);
            
            $request->session()->put('termos_aceitos', true); // Backup na sessão

            $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);

            if ($splitAtivo && !$user->split_configurado) {
                return redirect()->route('onboarding.split');
            }

            return redirect()->route('dashboard')->with('iniciar_tour', true);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ERRO_ACEITE_TERMOS: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Expondo o erro para diagnóstico em produção com prefixo único
            return back()->with('error', 'ERRO_FATAL_DIAGNOSTICO: ' . $e->getMessage());
        }
    }

    public function verSplit()
    {
        $user = auth()->user();
        
        if ($user->split_configurado) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.split');
    }

    public function ativarSplit(Request $request)
    {
        $request->validate([
            'asaas_wallet_id' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $user->vendedor->update([
            'asaas_wallet_id' => $request->asaas_wallet_id,
            'wallet_status' => 'pendente'
        ]);

        $user->update(['split_configurado' => true]);

        return redirect()->route('dashboard')->with('success', 'Configurações de Split enviadas para validação!');
    }

    public function pularSplit()
    {
        auth()->user()->update(['split_configurado' => true]);
        return redirect()->route('dashboard');
    }

    public function iniciarTour()
    {
        return view('onboarding.tour');
    }
}