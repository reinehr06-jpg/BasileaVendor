@props([
    'title' => '',
    'subtitle' => '',
    'icon' => 'fas fa-chart-bar',
    'exports' => [],
    'actions' => null,
])

<style>
    .page-hero-wrapper {
        margin-bottom: 24px;
        padding: 28px 32px;
        background: linear-gradient(135deg, #3B0764 0%, #4C1D95 100%);
        border-radius: 16px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 25px -5px rgba(59, 7, 100, 0.2);
        position: relative;
    }
    .page-hero-wrapper h2 { color: white; margin-bottom: 6px; font-size: 1.6rem; letter-spacing: -0.5px; }
    .page-hero-wrapper p { opacity: 0.85; font-size: 0.95rem; }
    
    .page-hero-actions { display: flex; align-items: center; gap: 12px; }
    
    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.25);
        cursor: pointer;
    }
    .hero-btn:hover { background: rgba(255, 255, 255, 0.25); }
    
    .hero-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .hero-dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 8px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        min-width: 160px;
        z-index: 99999;
        overflow: hidden;
    }
    
    .hero-dropdown-content.open { display: block; }
    
    .hero-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #334155;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.15s;
    }
    .hero-dropdown-item:hover { background: #f4f5fa; color: #4C1D95; }
    .hero-dropdown-item i { width: 20px; }
    .hero-dropdown-item.excel { color: #16a34a; }
    .hero-dropdown-item.pdf { color: #dc2626; }
    .hero-dropdown-item.csv { color: #2563eb; }
    
    @media (max-width: 768px) {
        .page-hero-wrapper { flex-direction: column; gap: 16px; text-align: center; padding: 24px; }
        .page-hero-actions { flex-wrap: wrap; justify-content: center; }
    }
</style>

<div style="margin-bottom: 24px;">
    <div class="page-hero-wrapper">
        <div>
            <h2><i class="{{ $icon }}" style="margin-right: 10px;"></i>{{ $title }}</h2>
            @if($subtitle)
            <p>{{ $subtitle }}</p>
            @endif
        </div>
        @if(count($exports) > 0 || $actions)
        <div class="page-hero-actions">
            @if(count($exports) > 0)
            <div class="hero-dropdown">
                <button type="button" class="hero-btn" id="exportBtn">
                    <i class="fas fa-file-export"></i> Exportar Dados
                    <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.8;"></i>
                </button>
                <div class="hero-dropdown-content" id="exportDropdown">
                    @foreach($exports as $exp)
                    <a href="{{ $exp['url'] }}" class="hero-dropdown-item {{ $exp['type'] ?? 'csv' }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('exportBtn');
    const dropdown = document.getElementById('exportDropdown');
    
    if (btn && dropdown) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });
        
        document.addEventListener('click', function(e) {
            if (!btn.parentElement.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }
});
</script>
