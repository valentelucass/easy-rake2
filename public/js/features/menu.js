/**
 * ==========================================================================
 * EASY RAKE - MENU MANAGEMENT
 * ==========================================================================
 * Gerenciamento consolidado de todos os menus mobile e desktop
 * ==========================================================================
 */

// Inicialização do menu mobile
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

// Inicialização do menu de caixa dashboard
function initializeCaixaDashboardMenu() {
    // Menu desktop
    const desktopTabs = document.querySelectorAll('.caixa-dashboard-menu .tab-btn');
    
    desktopTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active de todos os tabs
            desktopTabs.forEach(t => t.classList.remove('active'));
            
            // Adiciona active ao tab clicado
            this.classList.add('active');
            
            // Atualiza a URL com o tab selecionado
            const tabName = this.getAttribute('data-tab');
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('tab', tabName);
            window.history.pushState({}, '', currentUrl);
            
            // Carrega o conteúdo do tab
            loadTabContent(tabName);
        });
    });

    // Menu mobile
    const mobileTabs = document.querySelectorAll('#side-menu .menu-link');
    
    mobileTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            // Remove active de todos os tabs
            mobileTabs.forEach(t => t.classList.remove('active'));
            
            // Adiciona active ao tab clicado
            this.classList.add('active');
            
            // Fecha o menu mobile
            document.body.classList.remove('menu-open');
            
            // Atualiza a URL
            const href = this.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
    });
}

// Função para carregar conteúdo do tab
function loadTabContent(tabName) {
    // Implementar carregamento dinâmico do conteúdo
    console.log('Carregando conteúdo do tab:', tabName);
    
    // Aqui você pode implementar a lógica para carregar o conteúdo
    // baseado no tabName (fechamento, rake, fichas, etc.)
}

// Inicialização do menu de navegação principal
function initializeMainNavigation() {
    const tabButtons = document.querySelectorAll('.tabs-container .tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Remove active de todos os botões
            tabButtons.forEach(b => b.classList.remove('active'));
            
            // Adiciona active ao botão clicado
            this.classList.add('active');
            
            // Navega para a página correspondente
            const href = this.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
    });
}

// Função para inicializar todos os menus
function initializeAllMenus() {
    // Inicializa menu mobile
    initializeMobileMenu();
    
    // Inicializa navegação principal
    initializeMainNavigation();
    
    // Inicializa menu de caixa dashboard (se estiver na página)
    if (document.querySelector('.caixa-dashboard-menu')) {
        initializeCaixaDashboardMenu();
    }
    
    // Listener para redimensionamento da janela
    window.addEventListener('resize', function() {
        // Se mudou para mobile, inicializa o menu
        if (window.innerWidth <= 768) {
            initializeMobileMenu();
        }
    });
}

// Função de logout
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        window.location.href = '/api/auth/logout.php';
    }
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    initializeAllMenus();
    
    // Adiciona função de logout global
    window.logout = logout;
});

// Exporta funções para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initializeMobileMenu,
        initializeCaixaDashboardMenu,
        initializeMainNavigation,
        initializeAllMenus,
        loadTabContent,
        logout
    };
} 