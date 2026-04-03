@props([
    'title' => '',
    'subtitle' => '',
    'icon' => 'fas fa-chart-bar',
    'exports' => [],
    'actions' => null,
])

<style>
    .page-hero {
        margin-bottom: 24px;
        padding: 28px 32px;
        background: linear-gradient(135deg, var(--primary-dark) 0%, #4C1D95 100%);
        border-radius: var(--radius-xl);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
    }
    .page-hero h2 { color: white; margin-bottom: 6px; font-size: 1.6rem; letter-spacing: -0.5px; }
    .page-hero p { opacity: 0.85; font-size: 0.95rem; }
    .page-hero-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .page-hero-actions .export-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
    .page-hero-actions .export-buttons { display: flex; gap: 6px; }
    .hero-export-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 7px; font-weight: 600; font-size: 0.75rem; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
    .hero-export-btn.excel { background: rgba(22, 163, 74, 0.2); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.3); }
    .hero-export-btn.excel:hover { background: rgba(22, 163, 74, 0.35); }
    .hero-export-btn.pdf { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .hero-export-btn.pdf:hover { background: rgba(239, 68, 68, 0.35); }
    .hero-export-btn.csv { background: rgba(37, 99, 235, 0.2); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.3); }
    .hero-export-btn.csv:hover { background: rgba(37, 99, 235, 0.35); }
    @media (max-width: 768px) {
        .page-hero { flex-direction: column; gap: 16px; text-align: center; padding: 24px; }
        .page-hero-actions { align-items: center; }
    }
</style>

<div class="animate-up" style="animation-delay: 0.1s;">
    <div class="page-hero">
        <div>
            <h2><i class="{{ $icon }}" style="margin-right: 10px;"></i>{{ $title }}</h2>
            @if($subtitle)
            <p>{{ $subtitle }}</p>
            @endif
        </div>
        @if(count($exports) > 0 || $actions)
        <div class="page-hero-actions">
            @if(count($exports) > 0)
            <span class="export-label">Exportar</span>
            <div class="export-buttons">
                @foreach($exports as $exp)
                <a href="{{ $exp['url'] }}" class="hero-export-btn {{ $exp['type'] ?? 'csv' }}">
                    <i class="{{ $exp['icon'] ?? 'fas fa-file' }}"></i> {{ $exp['label'] ?? strtoupper($exp['type'] ?? 'CSV') }}
                </a>
                @endforeach
            </div>
            @endif
            @if($actions)
            {{ $actions }}
            @endif
        </div>
        @endif
    </div>
</div>
