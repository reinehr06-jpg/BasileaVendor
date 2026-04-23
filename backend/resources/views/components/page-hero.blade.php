@props([
    'title' => '',
    'subtitle' => '',
    'icon' => 'fas fa-chart-bar',
    'exports' => [],
])

<style>
    .page-hero-wrapper {
        margin-bottom: 24px;
        padding: 20px 0;
        background: transparent;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: none;
        position: relative;
    }
    .page-hero-wrapper h2 { color: var(--text-primary); margin-bottom: 4px; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; }
    .page-hero-wrapper p { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
    
    .page-hero-actions { display: flex; align-items: center; gap: 12px; }
    
    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
        background: white;
        color: var(--text-primary);
        border: 1px solid var(--border);
        cursor: pointer;
        box-shadow: var(--shadow-xs);
    }
    .hero-btn:hover { border-color: var(--primary); color: var(--primary); background: #f8fafc; }
    
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
        @if(count($exports) > 0 || $slot)
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
            {{ $slot }}
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
