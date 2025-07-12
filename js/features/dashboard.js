// Dashboard JavaScript - Funcionalidades principais

document.addEventListener('DOMContentLoaded', function() {
    // Inicialização dos formulários
    initializeForms();
    
    // Inicialização do menu mobile
    initializeMobileMenu();
    
    // Garantir funcionalidade mobile
    ensureMobileFunctionality();
    
    // Atualização automática de estatísticas
    updateStats();
    
    // Listener para redimensionamento da janela
    window.addEventListener('resize', function() {
        checkMobileView();
        ensureMobileFunctionality();
    });
});





// Função para inicializar formulários
function initializeForms() {
    // Formulário de abrir caixa
    const abrirCaixaForm = document.getElementById('abrir-caixa-form');
    if (abrirCaixaForm) {
        abrirCaixaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            abrirCaixa();
        });
    }
}

// Função para inicializar buscas
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        // Busca em tempo real após 500ms de pausa na digitação
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                buscarJogadores();
            }, 500);
        });
    }
}

// Função para abrir caixa
async function abrirCaixa() {
    const form = document.getElementById('abrir-caixa-form');
    const formData = new FormData(form);
    
    const data = {
        valor_inicial: parseFloat(formData.get('valor_inicial')),
        observacoes: formData.get('observacoes')
    };
    
    try {
        const response = await fetch('api/caixas/abrir_caixa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Sucesso!', result.message, 'success');
            form.reset();
            setTimeout(() => {
                updateStats();
                if (typeof carregarCaixasAbertos === 'function') carregarCaixasAbertos();
                if (typeof atualizarHistoricoCaixas === 'function') atualizarHistoricoCaixas(); // Atualiza o histórico
            }, 1500);
        } else {
            showNotification('Erro!', result.message, 'error');
        }
    } catch (error) {
        showNotification('Erro!', 'Erro de conexão. Tente novamente.', 'error');
    }
}

// Função para atualizar estatísticas
async function updateStats() {
    try {
        const response = await fetch('api/dashboard/get_stats.php');
        const result = await response.json();
        
        if (result.success) {
            // Atualiza os números das estatísticas
            const statNumbers = document.querySelectorAll('.stat-number');
            if (statNumbers.length >= 3) {
                statNumbers[0].textContent = result.stats.caixas_abertos || 0;
                statNumbers[1].textContent = result.stats.jogadores_ativos || 0;
                statNumbers[2].textContent = result.stats.aprovacoes_pendentes || 0;
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar estatísticas:', error);
    }
}

// Removido: funções duplicadas de notificação. Usar window.showNotification e window.showExportFeedback de notifications.js

// Função para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Função para formatar data
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

// Função para inicializar o menu mobile
function initializeMobileMenu() {
    // Só inicializa se estiver em viewport mobile
    if (window.innerWidth > 768) {
        return;
    }

    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideMenu = document.getElementById('side-menu');
    const menuOverlay = document.getElementById('menu-overlay');
    const body = document.body;
    const menuLinks = document.querySelectorAll('#side-menu .menu-link');

    function toggleMenu() {
        body.classList.toggle('menu-open');
    }

    // Toggle do menu ao clicar no hamburger
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', toggleMenu);
    }

    // Fechar menu ao clicar no overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', toggleMenu);
    }

    // Navegação do menu mobile - links diretos para páginas
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove classe active de todos os links
            menuLinks.forEach(l => l.classList.remove('active'));
            
            // Adiciona classe active ao link clicado
            this.classList.add('active');
            
            // Fecha o menu após a navegação
            body.classList.remove('menu-open');
        });
    });

    // Fechar menu ao pressionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('menu-open')) {
            toggleMenu();
        }
    });
}

// Função para verificar se está em mobile e reinicializar se necessário
function checkMobileView() {
    if (window.innerWidth <= 768) {
        // Se mudou para mobile, inicializa o menu
        initializeMobileMenu();
        // Sincroniza estado
        syncInitialState();
    }
}

// Função para garantir que todas as funcionalidades funcionem em mobile
function ensureMobileFunctionality() {
    // Garante que formulários funcionem em mobile
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (!form.hasAttribute('data-mobile-initialized')) {
            form.setAttribute('data-mobile-initialized', 'true');
            
            // Adiciona listeners específicos para mobile se necessário
            if (window.innerWidth <= 768) {
                // Ajustes específicos para mobile podem ser adicionados aqui
            }
        }
    });
    
    // Garante que botões funcionem em mobile
    const buttons = document.querySelectorAll('.button');
    buttons.forEach(button => {
        if (!button.hasAttribute('data-mobile-initialized')) {
            button.setAttribute('data-mobile-initialized', 'true');
            
            // Adiciona touch feedback para mobile
            if (window.innerWidth <= 768) {
                button.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                button.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            }
        }
    });
}