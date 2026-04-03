@extends('layouts.app')
@section('title', '2FA Bloqueado')

@section('content')
<style>
    .locked-container { max-width: 480px; margin: 60px auto; }
    .locked-card { background: white; border-radius: 16px; padding: 40px; text-align: center; border: 2px solid #fee2e2; box-shadow: 0 4px 16px rgba(239,68,68,0.1); }
    .locked-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fee2e2, #fecaca); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; }
    .locked-card h2 { font-size: 1.4rem; font-weight: 800; color: #991b1b; margin-bottom: 12px; }
    .locked-card p { color: #6b7280; font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px; }
    .locked-card .timer { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin: 20px 0; font-size: 2rem; font-weight: 800; color: #dc2626; font-family: monospace; }
    .locked-card .btn-back { background: #ef4444; color: white; border: none; padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
    .locked-card .btn-back:hover { background: #dc2626; transform: translateY(-2px); }
</style>

<div class="locked-container">
    <div class="locked-card">
        <div class="locked-icon">🔒</div>
        <h2>Autenticação Bloqueada</h2>
        <p>Muitas tentativas incorretas de código 2FA. Sua conta foi temporariamente bloqueada por segurança.</p>
        <div class="timer" id="timer">{{ $minutes }}:00</div>
        <p style="font-size: 0.82rem;">Tente novamente após o tempo expirar.</p>
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 16px;">
            @csrf
            <button type="submit" class="btn-back"><i class="fas fa-right-from-bracket"></i> Voltar ao Login</button>
        </form>
    </div>
</div>

<script>
(function() {
    var totalSeconds = {{ $minutes }} * 60;
    var timerEl = document.getElementById('timer');
    var interval = setInterval(function() {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(interval);
            window.location.reload();
            return;
        }
        var m = Math.floor(totalSeconds / 60);
        var s = totalSeconds % 60;
        timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    }, 1000);
})();
</script>
@endsection
