@extends('layouts.app')
@section('title', 'Termos e Documentos')

@section('content')
<style>
.terms-page {
    max-width: 900px;
    margin: 0 auto;
}

.terms-header {
    margin-bottom: 24px;
}

.terms-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b3b5c;
    margin-bottom: 8px;
}

.terms-header p {
    color: #717191;
    font-size: 0.9rem;
}

.term-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #ededf2;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 2px 4px rgba(50,50,71,0.04);
    transition: 0.2s;
}

.term-card:hover {
    box-shadow: 0 4px 12px rgba(50,50,71,0.08);
    transform: translateY(-1px);
}

.term-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.term-info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #3b3b5c;
    margin-bottom: 4px;
}

.term-info .version {
    color: #4C1D95;
    font-weight: 600;
    font-size: 0.85rem;
    background: #f3e8ff;
    padding: 2px 8px;
    border-radius: 6px;
    display: inline-block;
}

.term-info .date {
    color: #a1a1b5;
    font-size: 0.8rem;
    margin-top: 4px;
}

.term-actions {
    display: flex;
    gap: 8px;
}

.btn-download {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s;
    border: none;
    cursor: pointer;
}

.btn-pdf {
    background: #ef4444;
    color: white;
}

.btn-pdf:hover {
    background: #dc2626;
    color: white;
}

.btn-html {
    background: #2563eb;
    color: white;
}

.btn-html:hover {
    background: #1d4ed8;
    color: white;
}

.term-preview {
    background: #fafafa;
    border-radius: 10px;
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e0e0e8;
}

.term-preview::-webkit-scrollbar {
    width: 6px;
}

.term-preview::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.term-preview::-webkit-scrollbar-thumb {
    background: #c1c1d1;
    border-radius: 3px;
}

.term-preview h1, .term-preview h2, .term-preview h3 {
    color: #3b3b5c;
    margin-bottom: 12px;
}

.term-preview p {
    color: #525278;
    line-height: 1.6;
    margin-bottom: 12px;
}

.no-terms {
    text-align: center;
    padding: 60px 20px;
    color: #a1a1b5;
}

.no-terms i {
    font-size: 3rem;
    margin-bottom: 16px;
    color: #d1d1e0;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #4C1D95;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 16px;
}

.back-btn:hover {
    color: #7c3aed;
}
</style>

<div class="terms-page">
    <a href="{{ route('vendedor.configuracoes') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i> Voltar para configurações
    </a>

    <div class="terms-header">
        <h1><i class="fas fa-file-contract"></i> Termos e Documentos</h1>
        <p>Visualize e baixe os termos de uso e políticas vigentes</p>
    </div>

    @if($termos->isEmpty())
        <div class="no-terms">
            <i class="fas fa-file-alt"></i>
            <h3>Nenhum termo disponível</h3>
            <p>Não há termos vigentes no momento.</p>
        </div>
    @else
        @foreach($termos as $termo)
        <div class="term-card">
            <div class="term-header">
                <div class="term-info">
                    <h3>{{ $termo->titulo }}</h3>
                    <span class="version">Versão {{ $termo->versao }}</span>
                    <div class="date">
                        <i class="fas fa-calendar"></i> 
                        Publicado em {{ $termo->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="term-actions">
                    <a href="{{ route('vendedor.configuracoes.termos.pdf', $termo) }}" class="btn-download btn-pdf">
                        <i class="fas fa-file-pdf"></i> Baixar PDF
                    </a>
                    <a href="{{ route('vendedor.configuracoes.termos.html', $termo) }}" class="btn-download btn-html">
                        <i class="fas fa-file-code"></i> Baixar HTML
                    </a>
                </div>
            </div>
            <div class="term-preview">
                {!! $termo->conteudo_html !!}
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection