@extends('layouts.app')

@section('title', 'Atualização do Sistema')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-surface rounded-3xl border border-border/50 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-foreground mb-6">Atualização do Sistema</h1>

        <div class="mb-8 p-6 bg-amber-50 border border-amber-200 rounded-2xl">
            <h3 class="font-bold text-amber-800 mb-3">⚠️ Atenção</h3>
            <p class="text-amber-700 text-sm">
                Siga estes passos cuidadosamente para evitar perda de dados. Faça backup completo antes de começar.
            </p>
        </div>

        <div class="space-y-4 mb-8">
            <h3 class="text-lg font-bold text-foreground">Passos para atualização:</h3>
            @foreach($instructions as $step)
            <div class="flex items-start gap-4 p-4 bg-background border border-border/30 rounded-xl">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-sm">
                    {{ strtoupper(substr($step, 0, 1)) }}
                </span>
                <p class="text-purple-600/80 pt-1 font-mono text-sm">{{ $step }}</p>
            </div>
            @endforeach
        </div>

        <div class="flex gap-4">
            <a href="{{ $changelogUrl }}" target="_blank"
               class="bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primary/90 transition shadow">
               Ver changelog no GitHub
            </a>
            <a href="{{ route('master.dashboard') }}"
               class="bg-surface-hover text-purple-600 px-6 py-3 rounded-xl font-bold border border-border/50 hover:bg-surface transition">
               Voltar ao dashboard
            </a>
        </div>
    </div>
</div>
@endsection
