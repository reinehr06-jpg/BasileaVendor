@extends('layouts.app')
@section('title', 'Calendário da Equipe')

@section('content')
<style>
.cal-wrap{display:grid;grid-template-columns:1fr 340px;gap:0;height:calc(100vh - 120px);max-height:calc(100vh - 120px);overflow:hidden}
.cal-main{display:flex;flex-direction:column;overflow:hidden}
.cal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 28px 16px;border-bottom:1px solid var(--border-light)}
.cal-title{font-size:1.4rem;font-weight:800;color:var(--text-primary)}
.cal-nav{display:flex;align-items:center;gap:8px}
.cal-nav-btn{width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:var(--surface);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);transition:all .2s;font-size:.9rem}
.cal-nav-btn:hover{border-color:var(--primary);color:var(--primary)}
.cal-month-label{font-size:1.1rem;font-weight:700;color:var(--text-primary);min-width:180px;text-align:center}
.cal-today-btn{padding:8px 16px;border-radius:8px;border:1px solid var(--primary);background:transparent;color:var(--primary);font-weight:600;font-size:.8rem;cursor:pointer;transition:all .2s}
.cal-today-btn:hover{background:var(--primary);color:#fff}
.cal-grid-wrap{flex:1;overflow-y:auto;padding:0 16px 16px}
.cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);text-align:center;padding:12px 0 8px;position:sticky;top:0;background:var(--bg);z-index:2}
.cal-weekday{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)}
.cal-days{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}
.cal-day{min-height:90px;background:var(--surface);border-radius:10px;padding:6px 8px;cursor:pointer;transition:all .15s;border:2px solid transparent}
.cal-day:hover{border-color:rgba(76,29,149,.3)}
.cal-day.today{border-color:var(--primary);background:rgba(76,29,149,.04)}
.cal-day.selected{border-color:var(--primary);box-shadow:0 0 0 3px rgba(76,29,149,.15)}
.cal-day.other-month{opacity:.35}
.cal-day-num{font-size:.85rem;font-weight:700;color:var(--text-primary);margin-bottom:4px}
.cal-day.today .cal-day-num{background:var(--primary);color:#fff;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem}
.cal-event-chip{font-size:.65rem;padding:2px 6px;border-radius:4px;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600}
.chip-follow_up{background:rgba(76,29,149,.12);color:#4C1D95}
.chip-reuniao{background:rgba(22,177,255,.12);color:#0284C7}
.chip-lembrete{background:rgba(255,180,0,.15);color:#B45309}
.chip-vencimento{background:rgba(255,76,81,.12);color:#DC2626}
.cal-more{font-size:.6rem;color:var(--primary);font-weight:700}
.cal-panel{background:var(--surface);border-left:1px solid var(--border-light);display:flex;flex-direction:column;overflow-y:auto}
.cal-panel-header{padding:20px 24px;border-bottom:1px solid var(--border-light)}
.cal-panel-date{font-size:1.15rem;font-weight:800;color:var(--text-primary)}
.cal-panel-sub{font-size:.78rem;color:var(--text-muted);margin-top:2px}
.cal-panel-events{flex:1;padding:16px 20px;overflow-y:auto}
.cal-panel-empty{text-align:center;padding:40px 20px;color:var(--text-muted)}
.cal-panel-empty i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.4}
.cal-evt{padding:14px 16px;background:var(--bg);border-radius:12px;margin-bottom:10px;border-left:4px solid var(--primary);transition:all .15s}
.cal-evt:hover{box-shadow:0 2px 8px rgba(0,0,0,.06)}
.cal-evt-time{font-size:.75rem;font-weight:700;color:var(--primary);margin-bottom:4px}
.cal-evt-title{font-weight:700;color:var(--text-primary);font-size:.9rem}
.cal-evt-desc{font-size:.78rem;color:var(--text-secondary);margin-top:4px}
.cal-evt-meta{display:flex;align-items:center;gap:8px;margin-top:8px;flex-wrap:wrap}
.cal-evt-badge{font-size:.65rem;padding:3px 8px;border-radius:6px;font-weight:600}
.cal-evt-actions{display:flex;gap:6px;margin-top:10px}
.cal-evt-actions a,.cal-evt-actions button{padding:6px 10px;border-radius:6px;font-size:.72rem;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all .15s}
.cal-evt-actions a:hover,.cal-evt-actions button:hover{border-color:var(--primary);color:var(--primary)}
.cal-evt-actions .btn-ics{border-color:#059669;color:#059669}
.cal-evt-actions .btn-ics:hover{background:#059669;color:#fff}
.cal-new-btn{position:fixed;bottom:32px;right:380px;width:52px;height:52px;border-radius:50%;background:var(--primary);color:#fff;border:none;font-size:1.3rem;cursor:pointer;box-shadow:0 4px 20px rgba(76,29,149,.4);transition:all .2s;display:flex;align-items:center;justify-content:center;z-index:30}
.cal-new-btn:hover{transform:scale(1.1)}
.cal-modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(50,50,71,.6);display:none;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px)}
.cal-modal-overlay.active{display:flex}
.cal-modal{background:var(--surface);border-radius:16px;width:100%;max-width:500px;box-shadow:var(--shadow-xl)}
.cal-modal-head{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--border-light)}
.cal-modal-head h3{font-size:1.15rem;font-weight:700}
.cal-modal-close{width:32px;height:32px;border-radius:50%;border:none;background:var(--bg);cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;color:var(--text-muted)}
.cal-modal-close:hover{background:var(--danger-light);color:var(--danger)}
.cal-modal form{padding:24px}
.cal-field{margin-bottom:16px}
.cal-field label{display:block;font-weight:600;font-size:.85rem;margin-bottom:6px}
.cal-field input,.cal-field select,.cal-field textarea{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;font-family:var(--font)}
.cal-field input:focus,.cal-field select:focus,.cal-field textarea:focus{outline:none;border-color:var(--primary)}
.cal-field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cal-modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.cal-btn{padding:10px 20px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.88rem;border:1px solid var(--border);transition:all .15s}
.cal-btn-cancel{background:var(--bg);color:var(--text-primary)}
.cal-btn-save{background:var(--primary);color:#fff;border-color:var(--primary)}
@media(max-width:900px){.cal-wrap{grid-template-columns:1fr;height:auto}.cal-panel{border-left:none;border-top:1px solid var(--border-light)}.cal-new-btn{right:24px}}
</style>

@php
    $todosEventos = $meusEventos->merge($eventosEquipe)->unique('id');
    $eventosJson = $todosEventos->map(function($e) {
        return [
            'id' => $e->id, 'titulo' => $e->titulo, 'tipo' => $e->tipo, 'status' => $e->status,
            'descricao' => $e->descricao,
            'data_inicio' => $e->data_hora_inicio ? $e->data_hora_inicio->format('Y-m-d') : null,
            'hora_inicio' => $e->data_hora_inicio ? $e->data_hora_inicio->format('H:i') : null,
            'hora_fim' => $e->data_hora_fim ? $e->data_hora_fim->format('H:i') : null,
            'contato' => $e->contato ? $e->contato->nome : null,
            'usuario' => $e->usuario ? $e->usuario->name : null,
            'ics_url' => route('calendario.ics', $e->id),
        ];
    })->values()->toArray();
@endphp

<div class="cal-wrap">
    <div class="cal-main">
        <div class="cal-header">
            <div class="cal-nav">
                <button class="cal-nav-btn" onclick="calNav(-1)"><i class="fas fa-chevron-left"></i></button>
                <span class="cal-month-label" id="calMonthLabel"></span>
                <button class="cal-nav-btn" onclick="calNav(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
            <button class="cal-today-btn" onclick="calToday()"><i class="fas fa-crosshairs"></i> Hoje</button>
        </div>
        <div class="cal-grid-wrap">
            <div class="cal-weekdays"><div class="cal-weekday">Dom</div><div class="cal-weekday">Seg</div><div class="cal-weekday">Ter</div><div class="cal-weekday">Qua</div><div class="cal-weekday">Qui</div><div class="cal-weekday">Sex</div><div class="cal-weekday">Sáb</div></div>
            <div class="cal-days" id="calDays"></div>
        </div>
    </div>
    <div class="cal-panel">
        <div class="cal-panel-header"><div class="cal-panel-date" id="panelDate">Selecione um dia</div><div class="cal-panel-sub" id="panelSub"></div></div>
        <div class="cal-panel-events" id="panelEvents"><div class="cal-panel-empty"><i class="fas fa-calendar-check"></i><p>Clique em um dia para ver os eventos</p></div></div>
    </div>
</div>

<button class="cal-new-btn" onclick="openModal()" title="Novo Evento"><i class="fas fa-plus"></i></button>

<div class="cal-modal-overlay" id="calModal">
    <div class="cal-modal">
        <div class="cal-modal-head"><h3><i class="fas fa-calendar-plus" style="color:var(--primary);margin-right:8px"></i>Novo Evento</h3><button class="cal-modal-close" onclick="closeModal()">&times;</button></div>
        <form action="{{ route('calendario.store') }}" method="POST">@csrf
            <div class="cal-field"><label>Título *</label><input type="text" name="titulo" required placeholder="Ex: Reunião com cliente"></div>
            <div class="cal-field"><label>Tipo *</label><select name="tipo" required><option value="follow_up">📌 Follow-up</option><option value="reuniao">🤝 Reunião</option><option value="lembrete">🔔 Lembrete</option><option value="vencimento">⚠️ Vencimento</option></select></div>
            <div class="cal-field-row"><div class="cal-field"><label>Início *</label><input type="datetime-local" name="data_hora_inicio" id="modalInicio" required></div><div class="cal-field"><label>Fim</label><input type="datetime-local" name="data_hora_fim"></div></div>
            <div class="cal-field"><label>Descrição</label><textarea name="descricao" rows="3" placeholder="Detalhes..."></textarea></div>
            <div class="cal-modal-actions"><button type="button" class="cal-btn cal-btn-cancel" onclick="closeModal()">Cancelar</button><button type="submit" class="cal-btn cal-btn-save"><i class="fas fa-check"></i> Salvar</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const EVENTOS=@json($eventosJson);const MESES=['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];const DIAS_SEMANA=['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];let curYear,curMonth,selectedDate=null;
function init(){const n=new Date();curYear=n.getFullYear();curMonth=n.getMonth();render()}
function render(){document.getElementById('calMonthLabel').textContent=MESES[curMonth]+' '+curYear;const g=document.getElementById('calDays');g.innerHTML='';const f=new Date(curYear,curMonth,1).getDay(),d=new Date(curYear,curMonth+1,0).getDate(),p=new Date(curYear,curMonth,0).getDate(),t=new Date(),ts=t.getFullYear()+'-'+String(t.getMonth()+1).padStart(2,'0')+'-'+String(t.getDate()).padStart(2,'0');for(let i=f-1;i>=0;i--){const x=p-i,pm=curMonth===0?11:curMonth-1,py=curMonth===0?curYear-1:curYear,ds=py+'-'+String(pm+1).padStart(2,'0')+'-'+String(x).padStart(2,'0');g.appendChild(mkDay(x,ds,true))}for(let i=1;i<=d;i++){const ds=curYear+'-'+String(curMonth+1).padStart(2,'0')+'-'+String(i).padStart(2,'0');const c=mkDay(i,ds,false);if(ds===ts)c.classList.add('today');if(ds===selectedDate)c.classList.add('selected');g.appendChild(c)}const tc=g.children.length,rm=(Math.ceil(tc/7)*7)-tc;for(let i=1;i<=rm;i++){const nm=curMonth===11?0:curMonth+1,ny=curMonth===11?curYear+1:curYear,ds=ny+'-'+String(nm+1).padStart(2,'0')+'-'+String(i).padStart(2,'0');g.appendChild(mkDay(i,ds,true))}}
function mkDay(n,ds,o){const c=document.createElement('div');c.className='cal-day'+(o?' other-month':'');c.onclick=()=>selectDay(ds);const num=document.createElement('div');num.className='cal-day-num';num.textContent=n;c.appendChild(num);const evs=EVENTOS.filter(e=>e.data_inicio===ds);evs.slice(0,3).forEach(e=>{const ch=document.createElement('div');ch.className='cal-event-chip chip-'+e.tipo;ch.textContent=e.hora_inicio+' '+e.titulo;c.appendChild(ch)});if(evs.length>3){const m=document.createElement('div');m.className='cal-more';m.textContent='+'+(evs.length-3)+' mais';c.appendChild(m)}return c}
function selectDay(ds){selectedDate=ds;render();const d=new Date(ds+'T12:00:00');document.getElementById('panelDate').textContent=d.getDate()+' de '+MESES[d.getMonth()];document.getElementById('panelSub').textContent=DIAS_SEMANA[d.getDay()]+', '+d.getFullYear();const evs=EVENTOS.filter(e=>e.data_inicio===ds),p=document.getElementById('panelEvents');if(!evs.length){p.innerHTML='<div class="cal-panel-empty"><i class="fas fa-sun"></i><p>Nenhum evento neste dia</p></div>';return}const sc={agendado:'#2563EB',concluido:'#059669',cancelado:'#DC2626',faltou:'#D97706'},tl={follow_up:'Follow-up',reuniao:'Reunião',lembrete:'Lembrete',vencimento:'Vencimento'},bc={follow_up:'#4C1D95',reuniao:'#0284C7',lembrete:'#B45309',vencimento:'#DC2626'};p.innerHTML=evs.map(e=>'<div class="cal-evt" style="border-left-color:'+(bc[e.tipo]||'var(--primary)')+'"><div class="cal-evt-time"><i class="fas fa-clock"></i> '+e.hora_inicio+(e.hora_fim?' – '+e.hora_fim:'')+'</div><div class="cal-evt-title">'+e.titulo+'</div>'+(e.descricao?'<div class="cal-evt-desc">'+e.descricao+'</div>':'')+'<div class="cal-evt-meta"><span class="cal-evt-badge chip-'+e.tipo+'">'+(tl[e.tipo]||e.tipo)+'</span><span class="cal-evt-badge" style="background:'+(sc[e.status]||'#666')+'22;color:'+(sc[e.status]||'#666')+'">'+e.status.charAt(0).toUpperCase()+e.status.slice(1)+'</span>'+(e.usuario?'<span style="font-size:.72rem;color:var(--text-muted)"><i class="fas fa-headset"></i> '+e.usuario+'</span>':'')+'</div><div class="cal-evt-actions"><a href="'+e.ics_url+'" class="btn-ics"><i class="fas fa-download"></i> Calendário</a></div></div>').join('')}
function calNav(d){curMonth+=d;if(curMonth>11){curMonth=0;curYear++}if(curMonth<0){curMonth=11;curYear--}render()}
function calToday(){const n=new Date();curYear=n.getFullYear();curMonth=n.getMonth();selectedDate=curYear+'-'+String(curMonth+1).padStart(2,'0')+'-'+String(n.getDate()).padStart(2,'0');render();selectDay(selectedDate)}
function openModal(){document.getElementById('calModal').classList.add('active');if(selectedDate)document.getElementById('modalInicio').value=selectedDate+'T09:00'}
function closeModal(){document.getElementById('calModal').classList.remove('active')}
document.getElementById('calModal').addEventListener('click',function(e){if(e.target===this)closeModal()});
document.addEventListener('DOMContentLoaded',init);
</script>
@endsection