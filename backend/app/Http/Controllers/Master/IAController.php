<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IAController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->get('filtro', 'todos');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $tarefa = $request->get('tarefa');
        
        $query = DB::table('ai_logs')
            ->select('ai_logs.*', 'users.name as user_name', 'users.email as user_email')
            ->leftJoin('users', 'ai_logs.user_id', '=', 'users.id');
        
        // filtros por data
        if ($dataInicio) {
            $query->where('ai_logs.executado_em', '>=', $dataInicio);
        }
        if ($dataFim) {
            $query->where('ai_logs.executado_em', '<=', $dataFim . ' 23:59:59');
        }
        
        // filtro por tarefa
        if ($tarefa && $tarefa !== 'todos') {
            $query->where('ai_logs.tarefa', $tarefa);
        }
        
        // filtros por tipo
        switch ($filtro) {
            case 'gestor':
                $query->whereIn('users.perfil', ['gestor', 'master']);
                break;
            case 'vendedor':
                $query->where('users.perfil', 'vendedor');
                break;
            case 'erros':
                $query->where('ai_logs.sucesso', false);
                break;
        }
        
        // Ordenar por mais recente
        $logs = $query->orderBy('ai_logs.executado_em', 'desc')
            ->limit(500)
            ->get();
        
        // Estatísticas gerais
        $stats = $this->calcularStats($query);
        
        // Lista de usuários com métricas
        $usuarios = $this->calcularMetricasPorUsuario($query);
        
        // Lista de tarefas mais usadas
        $tarefasPopulares = DB::table('ai_logs')
            ->select('tarefa', DB::raw('count(*) as total'))
            ->when($dataInicio, fn($q) => $q->where('executado_em', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->where('executado_em', '<=', $dataFim))
            ->groupBy('tarefa')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        return view('master.ia.index', compact(
            'logs',
            'stats',
            'usuarios',
            'tarefasPopulares',
            'filtro',
            'dataInicio',
            'dataFim',
            'tarefa'
        ));
    }
    
    private function calcularStats($query)
    {
        $total = DB::table('ai_logs')->count();
        $sucesso = DB::table('ai_logs')->where('sucesso', true)->count();
        $erros = DB::table('ai_logs')->where('sucesso', false)->count();
        $mediaTempo = DB::table('ai_logs')->avg('duracao_ms') ?? 0;
        
        return [
            'total' => $total,
            'sucesso' => $sucesso,
            'erros' => $erros,
            'taxaSucesso' => $total > 0 ? round(($sucesso / $total) * 100, 1) : 0,
            'mediaTempo' => round($mediaTempo, 0),
        ];
    }
    
    private function calcularMetricasPorUsuario($query)
    {
        return DB::table('ai_logs')
            ->select(
                'users.id',
                'users.name',
                'users.perfil',
                DB::raw('count(ai_logs.id) as total_chamadas'),
                DB::raw('sum(case when ai_logs.sucesso = true then 1 else 0 end) as sucesso'),
                DB::raw('sum(case when ai_logs.sucesso = false then 1 else 0 end) as erro'),
                DB::raw('avg(ai_logs.duracao_ms) as tempo_medio'),
                DB::raw('max(ai_logs.executado_em) as ultima_acao')
            )
            ->leftJoin('users', 'ai_logs.user_id', '=', 'users.id')
            ->whereNotNull('ai_logs.user_id')
            ->groupBy('users.id', 'users.name', 'users.perfil')
            ->orderByDesc('total_chamadas')
            ->limit(20)
            ->get();
    }
    
    public function teste(Request $request)
    {
        $result = ['success' => false, 'message' => ''];
        
        try {
            $endpoint = config('services.ia_local.endpoint');
            $model = config('services.ia_local.model');
            
            if (!$endpoint) {
                throw new \Exception('Endpoint não configurado');
            }
            
            // Testar conexão
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post($endpoint . '/models', []);
            
            if ($response->successful()) {
                $result['success'] = true;
                $result['message'] = 'IA respondendo!';
                $result['model'] = $model;
            } else {
                throw new \Exception('IA não respondeu: ' . $response->status());
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }
        
        return response()->json($result);
    }
}