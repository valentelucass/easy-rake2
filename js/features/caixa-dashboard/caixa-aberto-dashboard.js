// js/features/caixa-dashboard/index.js

// Removido: função duplicada de logout. Usar window.logout do auth.js

// Menu sanduíche mobile
const hamburger = document.getElementById('hamburger-btn');
const sideMenu = document.getElementById('side-menu');
const overlay = document.getElementById('menu-overlay');
const links = document.querySelectorAll('#side-menu .menu-link');

if (hamburger) {
    hamburger.onclick = function() {
        document.body.classList.add('menu-open');
    };
}
if (overlay) {
    overlay.onclick = function() {
        document.body.classList.remove('menu-open');
    };
}
// SPA-like navegação mobile
links.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        // Remove active de todos
        links.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        // Troca conteúdo
        const url = new URL(this.href);
        const tab = url.searchParams.get('tab') || 'fechamento';
        document.querySelectorAll('.tab-content').forEach(tabDiv => tabDiv.classList.remove('active'));
        const content = document.getElementById('tab-content-' + tab);
        if (content) content.classList.add('active');
        // Fecha menu
        document.body.classList.remove('menu-open');
        // Atualiza URL sem recarregar
        window.history.pushState({}, '', '?id=' + window.CAIXA_ID + '&tab=' + tab);
    });
});
// Destacar link ativo ao carregar
(function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab') || 'fechamento';
    links.forEach(link => {
        if (link.href.includes('tab=' + tab)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
})();
// Suporte ao botão voltar/avançar do navegador
window.addEventListener('popstate', function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab') || 'fechamento';
    document.querySelectorAll('.tab-content').forEach(tabDiv => tabDiv.classList.remove('active'));
    const content = document.getElementById('tab-content-' + tab);
    if (content) content.classList.add('active');
    links.forEach(link => {
        if (link.href.includes('tab=' + tab)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
// Menu de abas desktop (caixa-dashboard-menu)
const desktopTabs = document.querySelectorAll('.caixa-dashboard-menu .tab-btn');
if (desktopTabs.length) {
    desktopTabs.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove active de todos
            desktopTabs.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            // Troca conteúdo
            const tab = this.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(tabDiv => tabDiv.classList.remove('active'));
            const content = document.getElementById('tab-content-' + tab);
            if (content) content.classList.add('active');
            // Atualiza URL sem recarregar
            window.history.pushState({}, '', '?id=' + window.CAIXA_ID + '&tab=' + tab);
            // Sincroniza menu mobile
            links.forEach(link => {
                if (link.href.includes('tab=' + tab)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    });
}
// Ao carregar, destacar aba desktop correta
(function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab') || 'fechamento';
    if (desktopTabs.length) {
        desktopTabs.forEach(btn => {
            if (btn.dataset.tab === tab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }
})();
// Suporte ao botão voltar/avançar para desktop também
window.addEventListener('popstate', function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab') || 'fechamento';
    // Conteúdo
    document.querySelectorAll('.tab-content').forEach(tabDiv => tabDiv.classList.remove('active'));
    const content = document.getElementById('tab-content-' + tab);
    if (content) content.classList.add('active');
    // Menu desktop
    if (desktopTabs.length) {
        desktopTabs.forEach(btn => {
            if (btn.dataset.tab === tab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }
    // Menu mobile
    links.forEach(link => {
        if (link.href.includes('tab=' + tab)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});



 