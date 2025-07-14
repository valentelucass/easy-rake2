// js/features/relatorios.js

document.addEventListener('DOMContentLoaded', function() {
    carregarDashboardRelatorios();
    carregarMovimentacoesRecentes();
    carregarGastosCategoria();
    carregarRankingJogadores();
    carregarHistoricoRelatoriosNovo();
});

// 1. Dashboard Resumido
function carregarDashboardRelatorios() {
    fetch('../src/api/relatorios/get_dashboard.php')
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.message || 'Erro ao buscar dashboard');
            renderizarDashboardRelatorios(json);
        })
        .catch(() => renderizarDashboardRelatorios(null));
}

function renderizarDashboardRelatorios(data) {
    let container = document.getElementById('relatorios-dashboard');
    if (!container) {
        container = document.createElement('div');
        container.id = 'relatorios-dashboard';
        container.className = 'dashboard-cards';
        const main = document.querySelector('.content-container .card-box.section');
        main.insertBefore(container, main.firstChild);
    }
    if (!data) {
        container.innerHTML = '<div class="dashboard-erro">Erro ao carregar resumo financeiro.</div>';
        return;
    }
    container.innerHTML = `
        <div class="dashboard-card saldo-atual">
            <span>Saldo Atual</span>
            <strong>R$ ${data.saldo_atual.toFixed(2)}</strong>
        </div>
        <div class="dashboard-card entradas">
            <span>Total Entradas</span>
            <strong>R$ ${data.total_entradas.toFixed(2)}</strong>
        </div>
        <div class="dashboard-card saidas">
            <span>Total Saídas</span>
            <strong>R$ ${data.total_saidas.toFixed(2)}</strong>
        </div>
        <div class="dashboard-card gastos">
            <span>Total Gastos</span>
            <strong>R$ ${data.total_gastos.toFixed(2)}</strong>
        </div>
        <div class="dashboard-card transacoes">
            <span>Transações Jogadores</span>
            <strong>${data.qtd_transacoes_jogadores}</strong>
        </div>
    `;
}

// 2. Movimentações Recentes
function carregarMovimentacoesRecentes() {
    fetch('../src/api/relatorios/get_movimentacoes.php')
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.message || 'Erro ao buscar movimentações');
            renderizarMovimentacoesRecentes(json.movimentacoes);
        })
        .catch(() => renderizarMovimentacoesRecentes(null));
}

function renderizarMovimentacoesRecentes(movs) {
    let container = document.getElementById('movimentacoes-recentes');
    if (!container) {
        container = document.createElement('div');
        container.id = 'movimentacoes-recentes';
        container.className = 'movimentacoes-section';
        const main = document.querySelector('.content-container .card-box.section');
        main.appendChild(container);
    }
    if (!movs) {
        container.innerHTML = '<div class="dashboard-erro">Erro ao carregar movimentações.</div>';
        return;
    }
    let html = '<h3>Últimas Movimentações</h3><table class="data-table"><thead><tr><th>Tipo</th><th>Valor</th><th>Descrição</th><th>Operador</th><th>Data</th></tr></thead><tbody>';
    if (movs.length === 0) {
        html += '<tr><td colspan="5">Nenhuma movimentação encontrada.</td></tr>';
    } else {
        movs.forEach(m => {
            html += `<tr><td>${m.tipo}</td><td>R$ ${m.valor.toFixed(2)}</td><td>${m.descricao}</td><td>${m.operador}</td><td>${m.data_movimentacao}</td></tr>`;
        });
    }
    html += '</tbody></table>';
    container.innerHTML = html;
}

// 3. Gastos por Categoria
function carregarGastosCategoria() {
    fetch('../src/api/relatorios/get_gastos_categoria.php')
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.message || 'Erro ao buscar gastos por categoria');
            renderizarGastosCategoria(json.categorias);
        })
        .catch(() => renderizarGastosCategoria(null));
}

function renderizarGastosCategoria(categorias) {
    let container = document.getElementById('gastos-categoria');
    if (!container) {
        container = document.createElement('div');
        container.id = 'gastos-categoria';
        container.className = 'gastos-section';
        const main = document.querySelector('.content-container .card-box.section');
        main.appendChild(container);
    }
    if (!categorias) {
        container.innerHTML = '<div class="dashboard-erro">Erro ao carregar gastos por categoria.</div>';
        return;
    }
    let html = '<h3>Gastos por Categoria</h3><table class="data-table"><thead><tr><th>Categoria</th><th>Total</th></tr></thead><tbody>';
    if (categorias.length === 0) {
        html += '<tr><td colspan="2">Nenhum gasto encontrado.</td></tr>';
    } else {
        categorias.forEach(c => {
            html += `<tr><td>${c.descricao}</td><td>R$ ${c.total.toFixed(2)}</td></tr>`;
        });
    }
    html += '</tbody></table>';
    container.innerHTML = html;
}

// 4. Ranking de Jogadores
function carregarRankingJogadores() {
    fetch('../src/api/relatorios/get_ranking_jogadores.php')
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.message || 'Erro ao buscar ranking de jogadores');
            renderizarRankingJogadores(json.ranking);
        })
        .catch(() => renderizarRankingJogadores(null));
}

function renderizarRankingJogadores(ranking) {
    let container = document.getElementById('ranking-jogadores');
    if (!container) {
        container = document.createElement('div');
        container.id = 'ranking-jogadores';
        container.className = 'ranking-section';
        const main = document.querySelector('.content-container .card-box.section');
        main.appendChild(container);
    }
    if (!ranking) {
        container.innerHTML = '<div class="dashboard-erro">Erro ao carregar ranking de jogadores.</div>';
        return;
    }
    let html = '<h3>Ranking de Jogadores</h3><table class="data-table"><thead><tr><th>Nome</th><th>CPF</th><th>Total Movimentado</th></tr></thead><tbody>';
    if (ranking.length === 0) {
        html += '<tr><td colspan="3">Nenhum jogador encontrado.</td></tr>';
    } else {
        ranking.forEach(j => {
            html += `<tr><td>${j.nome}</td><td>${j.cpf}</td><td>R$ ${j.total_movimentado.toFixed(2)}</td></tr>`;
        });
    }
    html += '</tbody></table>';
    container.innerHTML = html;
}

// 5. Histórico de Relatórios (novo endpoint)
function carregarHistoricoRelatoriosNovo() {
    fetch('../src/api/relatorios/get_historico_relatorios.php')
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.message || 'Erro ao buscar histórico de relatórios');
            renderizarHistoricoRelatoriosNovo(json.historico);
        })
        .catch(() => renderizarHistoricoRelatoriosNovo(null));
}

function renderizarHistoricoRelatoriosNovo(historico) {
    let container = document.getElementById('historico-relatorios');
    if (!container) {
        container = document.createElement('div');
        container.id = 'historico-relatorios';
        container.className = 'historico-section';
        const main = document.querySelector('.content-container .card-box.section');
        main.appendChild(container);
    }
    if (!historico) {
        container.innerHTML = '<div class="dashboard-erro">Erro ao carregar histórico de relatórios.</div>';
        return;
    }
    let html = '<h3>Histórico de Relatórios</h3><table class="data-table"><thead><tr><th>ID</th><th>Tipo</th><th>Status</th><th>Data</th><th>Arquivo</th></tr></thead><tbody>';
    if (historico.length === 0) {
        html += '<tr><td colspan="5">Nenhum relatório gerado ainda.</td></tr>';
    } else {
        historico.forEach(r => {
            html += `<tr><td>${r.id}</td><td>${r.tipo}</td><td>${r.status}</td><td>${r.data_geracao}</td><td>${r.arquivo ? `<a href='${r.arquivo}' target='_blank'>Baixar</a>` : '-'}</td></tr>`;
        });
    }
    html += '</tbody></table>';
    container.innerHTML = html;
} 