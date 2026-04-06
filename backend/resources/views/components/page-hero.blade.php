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
        position: relative;
        z-index: 10;
    }
    .page-hero h2 { color: white; margin-bottom: 6px; font-size: 1.6rem; letter-spacing: -0.5px; }
    .page-hero p { opacity: 0.85; font-size: 0.95rem; }
    .page-hero-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; position: relative; z-index: 9998; }
    .page-hero-actions .export-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; display: none; }
    
    .export-dropdown { position: relative; display: inline-block; z-index: 9999; }
    .hero-export-toggle { 
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; 
        border-radius: 7px; font-weight: 600; font-size: 0.8rem; text-decoration: none; 
        transition: 0.2s; cursor: pointer; 
        background: rgba(255, 255, 255, 0.15); color: white; border: 1px solid rgba(255, 255, 255, 0.2); 
    }
    .hero-export-toggle:hover { background: rgba(255, 255, 255, 0.25); }
    
    .export-dropdown-menu { 
        position: absolute; right: 0; top: 100%; margin-top: 10px;
        opacity: 0; visibility: hidden; transform: translateY(8px);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        background: #ffffff; border-radius: var(--radius-md); box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        padding: 6px; min-width: 160px; z-index: 9999; border: 1px solid #e2e8f0;
        pointer-events: none;
    }
    
    /* Ponte invisível que permite mover o mouse do botão para o menu sem perder o hover */
    .export-dropdown-menu::after {
        content: ''; position: absolute; top: -15px; left: 0; right: 0; height: 15px; background: transparent;
    }

    .export-dropdown-menu::before {
        content: ''; position: absolute; top: -5px; right: 18px;
        width: 10px; height: 10px; background: #fff; transform: rotate(45deg);
        border-top: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0;
    }

    .export-dropdown:hover .export-dropdown-menu { 
        opacity: 1; visibility: visible; transform: translateY(0); pointer-events: all;
    }

    .export-dropdown-item { 
        display: flex; align-items: center; gap: 10px; padding: 8px 12px;
        color: #334155; text-decoration: none; font-size: 0.85rem;
        font-weight: 600; border-radius: 6px; transition: 0.2s; position: relative;
    }
    .export-dropdown-item:hover { background: #f8fafc; color: var(--primary); }
    
    .export-dropdown-item.excel i { color: #16a34a; }
    .export-dropdown-item.pdf i { color: #dc2626; }
    .export-dropdown-item.csv i { color: #2563eb; }

    @media (max-width: 768px) {
        .page-hero { flex-direction: column; gap: 16px; text-align: center; padding: 24px; }
        .page-hero-actions { align-items: center; }
        .export-dropdown-menu { right: auto; left: 50%; transform: translateX(-50%); margin-top: 5px; }
        .export-dropdown-menu::before { right: auto; left: 50%; transform: translateX(-50%) rotate(45deg); }
        .export-dropdown:hover .export-dropdown-menu { transform: translateX(-50%); }
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
            <div class="export-dropdown">
                <button class="hero-export-toggle">
                    <i class="fas fa-file-export"></i> Exportar Dados <i class="fas fa-chevron-down" style="font-size: 0.6rem; opacity: 0.8; margin-left: 2px;"></i>
                </button>
                <div class="export-dropdown-menu">
                    @foreach($exports as $exp)
                    <a href="{{ $exp['url'] }}" class="export-dropdown-item {{ $exp['type'] ?? 'csv' }}">
                        <i class="{{ $exp['icon'] ?? 'fas fa-file' }}"></i> {{ $exp['label'] ?? strtoupper($exp['type'] ?? 'CSV') }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @if($actions)
            {{ $actions }}
            @endif
        </div>
        @endif
    </div>
</div>
