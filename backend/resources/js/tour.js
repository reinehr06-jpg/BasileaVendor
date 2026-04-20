import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

let tourInstance = null;
let podeAvancar = false;
let timerInterval = null;
let stepStartTime = null;

export function iniciarTour() {
    if (tourInstance) {
        tourInstance.complete();
    }

    tourInstance = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            cancelIcon: { enabled: true },
            scrollTo: { behavior: 'smooth', block: 'center' },
            classes: 'basilea-tour-step',
            modalOverlayOpeningPadding: 10,
            modalOverlayOpeningRadius: 10,
        },
    });

    const baseSteps = [
        {
            id: 'menu-lateral',
            title: '📌 Menu de Navegação',
            text: 'Aqui estão todos os módulos do sistema. Cada seção tem ícones específicos para facilitar sua navegação.',
            attachTo: { element: '.sidebar-menu', on: 'right' },
            buttons: [createButton('Começar tour →', 'next')],
        },
        {
            id: 'painel-principal',
            title: '📊 Painel Principal',
            text: 'Seu dashboard inicial mostra métricas importantes: faturamento, vendas, clientes e comissões. Use os filtros de período para analisar diferentes tempos.',
            attachTo: { element: '.welcome-section', on: 'bottom' },
            buttons: createNavigationButtons(),
        },
        {
            id: 'campanhas',
            title: '📣 Campanhas de Marketing',
            text: 'Acompanhe todas as suas campanhas de marketing. Veja CPL, taxa de conversão, leads recebidos e qual canal traz melhores resultados.',
            attachTo: { element: 'a[href*="campanhas"]', on: 'right' },
            buttons: createNavigationButtons(),
        },
        {
            id: 'contatos',
            title: '👥 Gestão de Contatos',
            text: 'Todos os seus leads e clientes em um só lugar. Filtre por status, campanha ou vendedor. Clique em um contato para ver detalhes.',
            attachTo: { element: 'a[href*="contatos"]', on: 'right' },
            buttons: createNavigationButtons(),
        },
        {
            id: 'calendario',
            title: '📅 Calendário de Eventos',
            text: 'Agende follow-ups, reuniões e lembretes. Você e sua equipe conseguem ver todos os eventos próximos.',
            attachTo: { element: 'a[href*="calendario"]', on: 'right' },
            buttons: createNavigationButtons(),
        },
        {
            id: 'configuracoes',
            title: '⚙️ Configurações',
            text: 'Aqui você gerencia seu perfil, split de pagamento, notificações e preferências do sistema.',
            attachTo: { element: 'a[href*="configuracoes"]', on: 'right' },
            buttons: createFinishButtons(),
        },
    ];

    baseSteps.forEach(step => tourInstance.addStep(step));

    tourInstance.on('show', () => {
        stepStartTime = Date.now();
        podeAvancar = false;
        iniciarTimer();
    });

    tourInstance.on('complete', () => {
        finalizarTour();
    });

    tourInstance.on('cancel', () => {
        finalizarTour();
    });

    tourInstance.start();
}

function createButton(text, action) {
    return [{
        text: text,
        action: () => {
            stepStartTime = Date.now();
            if (action === 'next') {
                tourInstance.next();
            } else if (action === 'back') {
                tourInstance.back();
            } else if (action === 'complete') {
                finalizarTour();
            }
        },
        classes: action === 'next' ? 'shepherd-btn-primary' : 'shepherd-btn-secondary',
    }];
}

function createNavigationButtons() {
    return [
        {
            text: '← Voltar',
            action: tourInstance.back,
            classes: 'shepherd-btn-secondary',
        },
        {
            text: 'Próximo →',
            action: () => tourInstance.next(),
            classes: 'shepherd-btn-primary',
        },
    ];
}

function createFinishButtons() {
    return [
        {
            text: '← Voltar',
            action: tourInstance.back,
            classes: 'shepherd-btn-secondary',
        },
        {
            text: '🎉 Concluir Tour!',
            action: () => finalizarTour(),
            classes: 'shepherd-btn-success',
        },
    ];
}

function iniciarTimer() {
    if (timerInterval) clearInterval(timerInterval);

    const botaoProximo = document.querySelector('.shepherd-btn-primary');
    if (!botaoProximo) return;

    botaoProximo.disabled = true;
    botaoProximo.style.opacity = '0.5';
    botaoProximo.style.pointerEvents = 'none';

    timerInterval = setInterval(() => {
        if (!stepStartTime) return;

        const tempoNaTela = (Date.now() - stepStartTime) / 1000;
        const tempoRestante = Math.max(0, 30 - Math.floor(tempoNaTela));

        if (tempoNaTela >= 30 && !podeAvancar) {
            podeAvancar = true;
            botaoProximo.disabled = false;
            botaoProximo.style.opacity = '1';
            botaoProximo.style.pointerEvents = 'auto';
            botaoProximo.innerHTML = 'Próximo →';
        } else if (!podeAvancar) {
            botaoProximo.innerHTML = `Aguarde ${tempoRestante}s...`;
        }
    }, 500);
}

function finalizarTour() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    if (tourInstance) {
        tourInstance.complete();
        tourInstance = null;
    }

    fetch('/onboarding/tour/completo', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    }).then(() => {
        console.log('Tour concluído e registrado!');
    }).catch(() => {
        console.log('Tour concluído (erro ao registrar)');
    });
}

window.iniciarTour = iniciarTour;