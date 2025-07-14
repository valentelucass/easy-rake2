/*
 * ==========================================================================
 * EASY RAKE - FECHAMENTO DE CAIXA JAVASCRIPT
 * ==========================================================================
 * Autor: Gemini
 * Descrição: Funcionalidades específicas para o fechamento de caixa,
 * incluindo resumo financeiro, encerramento e exportação.
 * ==========================================================================
 */

// --- Compilação de Dados de Caixa (Fechamento) ---
function renderCompilacaoTabela(dados) {
    if (!dados || !Array.isArray(dados.movimentacoes)) {
        document.getElementById('compilacao-caixa-tabela').innerHTML = '<div class="error-message">Nenhum dado encontrado.</div>';
        return;
    }
    let html = `<table class="data-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Valor (R$)</th>
                <th>Operador</th>
            </tr>
        </thead>
        <tbody>`;
    dados.movimentacoes.forEach(mov => {
        html += `<tr>
            <td>${mov.data_movimentacao}</td>
            <td>${mov.tipo}</td>
            <td>${mov.descricao}</td>
            <td>${Number(mov.valor).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
            <td>${mov.operador_nome || '-'}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    // Totais
    html += `<div class="compilacao-totais">
        <strong>Total de Receitas:</strong> R$ ${Number(dados.total_entradas).toLocaleString('pt-BR', {minimumFractionDigits:2})}<br>
        <strong>Total de Despesas:</strong> R$ ${Number(dados.total_saidas).toLocaleString('pt-BR', {minimumFractionDigits:2})}<br>
        <strong>Saldo Final:</strong> R$ ${Number(dados.saldo_final).toLocaleString('pt-BR', {minimumFractionDigits:2})}
    </div>`;
    document.getElementById('compilacao-caixa-tabela').innerHTML = html;
}

function carregarCompilacaoCaixa() {
    const loader = document.getElementById('compilacao-caixa-loader');
    const erro = document.getElementById('compilacao-caixa-erro');
    const tabela = document.getElementById('compilacao-caixa-tabela');
    loader.classList.remove('hidden');
    erro.classList.add('hidden');
    tabela.innerHTML = '';
    fetch(`api/caixas/compilar_dados.php?caixa_id=${window.CAIXA_ID}`)
        .then(r => r.json())
        .then(dados => {
            loader.classList.add('hidden');
            if (dados.erro) {
                erro.textContent = dados.erro;
                erro.classList.remove('hidden');
            } else {
                renderCompilacaoTabela(dados);
                // Atualizar resumo financeiro
                atualizarResumoFinanceiro(dados);
                atualizarDashboardSessaoAtiva(dados); // <-- NOVO: atualizar blocos detalhados
            }
        })
        .catch(() => {
            loader.classList.add('hidden');
            erro.textContent = 'Erro ao buscar dados do caixa.';
            erro.classList.remove('hidden');
        });
}

// --- Resumo Financeiro ---
function atualizarResumoFinanceiro(dados) {
    const valorInicial = dados.caixa?.valor_inicial || 0;
    const totalReceitas = dados.total_entradas || 0;
    const totalDespesas = dados.total_saidas || 0;
    const saldoAtual = dados.saldo_final || (valorInicial + totalReceitas - totalDespesas);

    document.getElementById('valor-inicial').textContent = `R$ ${Number(valorInicial).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
    document.getElementById('total-entradas').textContent = `R$ ${Number(totalReceitas).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
    document.getElementById('total-saidas').textContent = `R$ ${Number(totalDespesas).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
    document.getElementById('saldo-atual').textContent = `R$ ${Number(saldoAtual).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
}

// --- Status do Caixa ---
function carregarStatusCaixa() {
    fetch(`api/caixas/get_caixa_info.php?id=${window.CAIXA_ID}`)
        .then(r => r.json())
        .then(dados => {
            if (dados.success && dados.caixa) {
                const caixa = dados.caixa;
                document.getElementById('operador-caixa').textContent = caixa.operador_nome || '-';
                document.getElementById('data-abertura').textContent = caixa.data_abertura ? new Date(caixa.data_abertura).toLocaleString('pt-BR') : '-';
                
                // Atualizar status
                const statusElement = document.getElementById('status-caixa');
                if (caixa.status === 'Fechado') {
                    statusElement.textContent = 'FECHADO';
                    statusElement.className = 'status-badge status-fechado';
                    
                    // Desabilitar botão de encerrar e mostrar botão de voltar
                    const btnEncerrar = document.getElementById('btn-encerrar-caixa');
                    if (btnEncerrar) {
                        btnEncerrar.disabled = true;
                        btnEncerrar.textContent = 'Caixa Encerrado';
                        btnEncerrar.style.opacity = '0.6';
                    }
                    mostrarBotaoVoltar();
                } else {
                    statusElement.textContent = 'ABERTO';
                    statusElement.className = 'status-badge status-aberto';
                }
                
                // Calcular tempo aberto
                if (caixa.data_abertura) {
                    const abertura = new Date(caixa.data_abertura);
                    const agora = new Date();
                    const diff = agora - abertura;
                    const horas = Math.floor(diff / (1000 * 60 * 60));
                    const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    document.getElementById('tempo-aberto').textContent = `${horas}h ${minutos}min`;
                }
            }
        })
        .catch(() => {
            console.error('Erro ao carregar status do caixa');
        });
}

// --- Encerrar Caixa ---
function encerrarCaixa() {
    if (!confirm('Tem certeza que deseja encerrar este caixa? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    const btnEncerrar = document.getElementById('btn-encerrar-caixa');
    const textoOriginal = btnEncerrar.textContent;
    btnEncerrar.disabled = true;
    btnEncerrar.textContent = 'Encerrando...';
    
    fetch('../src/api/caixas/encerrar_caixa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: window.CAIXA_ID })
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            window.showNotification('Sucesso!', 'Caixa encerrado com sucesso!', 'success');
            // Atualizar status
            document.getElementById('status-caixa').textContent = 'FECHADO';
            document.getElementById('status-caixa').className = 'status-badge status-fechado';
            // Desabilitar botão
            btnEncerrar.disabled = true;
            btnEncerrar.textContent = 'Caixa Encerrado';
            btnEncerrar.style.opacity = '0.6';
            
            // Mostrar botão para voltar ao dashboard
            mostrarBotaoVoltar();
        } else {
            window.showNotification('Erro!', result.message || 'Erro ao encerrar caixa.', 'error');
            btnEncerrar.disabled = false;
            btnEncerrar.textContent = textoOriginal;
        }
    })
    .catch(() => {
        window.showNotification('Erro!', 'Erro de conexão ao encerrar caixa.', 'error');
        btnEncerrar.disabled = false;
        btnEncerrar.textContent = textoOriginal;
    });
}

// --- Mostrar Botão Voltar ---
function mostrarBotaoVoltar() {
    const acoesSection = document.getElementById('acoes-fechamento-section');
    if (acoesSection) {
        const botaoVoltar = document.createElement('div');
        botaoVoltar.className = 'acao-card';
        botaoVoltar.innerHTML = `
            <h4>Caixa Encerrado</h4>
            <p>O caixa foi encerrado com sucesso. Você pode voltar ao dashboard principal.</p>
            <button onclick="window.location.href='abrir-caixa.php'" class="button button--primary">Voltar ao Dashboard</button>
        `;
        acoesSection.querySelector('.acoes-fechamento-grid').appendChild(botaoVoltar);
    }
}

// --- Feedback de Exportação ---
function showExportFeedback(tipo, status, mensagem) {
    window.showExportFeedback(tipo, status, mensagem);
}

function bloquearExportacao(bloquear) {
    const btnPdf = document.getElementById('btn-exportar-pdf');
    const btnExcel = document.getElementById('btn-exportar-excel');
    
    if (bloquear) {
        btnPdf.disabled = true;
        btnExcel.disabled = true;
        btnPdf.textContent = 'Gerando...';
        btnExcel.textContent = 'Gerando...';
    } else {
        btnPdf.disabled = false;
        btnExcel.disabled = false;
        btnPdf.textContent = 'PDF';
        btnExcel.textContent = 'Excel';
    }
}

// --- Notificações ---
// Removido: funções duplicadas de notificação. Usar window.showNotification e window.showExportFeedback de notifications.js

// --- Dashboard Sessão Ativa (Blocos Detalhados) ---
function atualizarDashboardSessaoAtiva(dados) {
    // Inventário Detalhado
    if (dados.inventario) {
        document.getElementById('inventario-atual').textContent =
            (dados.inventario.inventario_atual !== null && dados.inventario.inventario_atual !== undefined)
                ? `R$ ${Number(dados.inventario.inventario_atual).toLocaleString('pt-BR', {minimumFractionDigits:2})}` : '—';
        document.getElementById('inventario-real').textContent =
            (dados.inventario.inventario_real !== null && dados.inventario.inventario_real !== undefined)
                ? `R$ ${Number(dados.inventario.inventario_real).toLocaleString('pt-BR', {minimumFractionDigits:2})}` : '—';
        let dif = dados.inventario.diferenca;
        let difText = '—';
        let difClass = '';
        if (dif !== null && dif !== undefined) {
            difText = `R$ ${Number(dif).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
            if (Math.abs(dif) < 0.01) difClass = 'correto';
            else if (dif < 0) difClass = 'falta';
            else if (dif > 0) difClass = 'sobra';
        }
        const difElem = document.getElementById('diferenca-inventario');
        difElem.textContent = difText;
        difElem.className = difClass;
    }
    // Receitas Detalhadas
    if (dados.receitas) {
        document.getElementById('rake-total').textContent =
            `R$ ${Number(dados.receitas.rake_total || 0).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
        document.getElementById('cashback-caixinhas').textContent =
            `R$ ${Number(dados.receitas.cashback_caixinhas || 0).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
        document.getElementById('receita-total').textContent =
            `R$ ${Number(dados.receitas.total || 0).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
    }
    // Caixinhas
    if (dados.caixinhas) {
        document.getElementById('caixinhas-bruto').textContent =
            `R$ ${Number(dados.caixinhas.total_bruto || 0).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
        document.getElementById('caixinhas-liquido').textContent =
            `R$ ${Number(dados.caixinhas.total_liquido || 0).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
        // Lista de caixinhas
        let html = '';
        if (Array.isArray(dados.caixinhas.lista)) {
            html += '<ul class="caixinhas-lista">';
            dados.caixinhas.lista.forEach(caixinha => {
                html += `<li><strong>${caixinha.nome}</strong>: Bruto R$ ${Number(caixinha.bruto).toLocaleString('pt-BR', {minimumFractionDigits:2})}, Líquido R$ ${Number(caixinha.liquido).toLocaleString('pt-BR', {minimumFractionDigits:2})}, Cashback R$ ${Number(caixinha.cashback).toLocaleString('pt-BR', {minimumFractionDigits:2})}, Por Participante R$ ${Number(caixinha.por_participante).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
                html += '</li>';
            });
            html += '</ul>';
        }
        document.getElementById('caixinhas-lista').innerHTML = html;
    }
    // Jogadores Ativos
    if (dados.jogadores) {
        document.getElementById('jogadores-total').textContent = dados.jogadores.total || 0;
        // Lista detalhada
        let html = '';
        if (Array.isArray(dados.jogadores.lista)) {
            html += '<table class="jogadores-lista-table"><thead><tr><th>Nome</th><th>Fichas Compradas</th><th>Devolvidas</th><th>Saldo</th><th>Situação</th></tr></thead><tbody>';
            dados.jogadores.lista.forEach(jog => {
                html += `<tr><td>${jog.nome}</td><td>${Number(jog.fichas_compradas).toLocaleString('pt-BR')}</td><td>${Number(jog.fichas_devolvidas).toLocaleString('pt-BR')}</td><td>${Number(jog.saldo_atual).toLocaleString('pt-BR')}</td><td>${jog.situacao}</td></tr>`;
            });
            html += '</tbody></table>';
        }
        document.getElementById('jogadores-lista').innerHTML = html;
    }
    // Saldo Operacional
    if (dados.saldo_operacional !== undefined && dados.saldo_operacional !== null) {
        document.getElementById('saldo-operacional').textContent =
            `R$ ${Number(dados.saldo_operacional).toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
    }
}

// --- Carregar Dados de Fechamento ---
function carregarDadosFechamento() {
    carregarCompilacaoCaixa();
    carregarStatusCaixa();
}

// --- Atualização automática do Dashboard Sessão Ativa ---
let dashboardSessaoAtivaInterval = null;

function iniciarAtualizacaoDashboardSessaoAtiva() {
    // Atualiza imediatamente
    carregarCompilacaoCaixa();
    // Limpa qualquer timer anterior
    if (dashboardSessaoAtivaInterval) clearInterval(dashboardSessaoAtivaInterval);
    // Atualiza a cada 30 segundos
    dashboardSessaoAtivaInterval = setInterval(() => {
        // Só atualiza se a aba de fechamento estiver ativa
        const tabFechamento = document.getElementById('tab-content-fechamento');
        if (!tabFechamento || tabFechamento.classList.contains('active')) {
            carregarCompilacaoCaixa();
        }
    }, 30000);
}

function pararAtualizacaoDashboardSessaoAtiva() {
    if (dashboardSessaoAtivaInterval) {
        clearInterval(dashboardSessaoAtivaInterval);
        dashboardSessaoAtivaInterval = null;
    }
}

// --- Integrar atualização ao trocar de aba ---
document.querySelectorAll('.caixa-dashboard-menu .tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        setTimeout(() => {
            const tabFechamento = document.getElementById('tab-content-fechamento');
            if (tabFechamento && tabFechamento.classList.contains('active')) {
                iniciarAtualizacaoDashboardSessaoAtiva();
            } else {
                pararAtualizacaoDashboardSessaoAtiva();
            }
        }, 100);
    });
});

// --- Integrar atualização ao DOMContentLoaded (caso já esteja na aba) ---
document.addEventListener('DOMContentLoaded', function() {
    const tabFechamento = document.getElementById('tab-content-fechamento');
    if (tabFechamento && tabFechamento.classList.contains('active')) {
        iniciarAtualizacaoDashboardSessaoAtiva();
    }
});

// --- Expor função para ações relevantes (ex: registrar gasto, inclusão caixinha) ---
window.atualizarDashboardSessaoAtiva = function() {
    carregarCompilacaoCaixa();
};

// --- Event Listeners ---
document.addEventListener('DOMContentLoaded', function() {
    // Listener para encerrar caixa
    const btnEncerrar = document.getElementById('btn-encerrar-caixa');
    if (btnEncerrar) {
        btnEncerrar.addEventListener('click', encerrarCaixa);
    }
    
    // Listener para atualizar dados
    const btnAtualizar = document.getElementById('btn-atualizar-dados');
    if (btnAtualizar) {
        btnAtualizar.addEventListener('click', carregarCompilacaoCaixa);
    }
    
    // Listeners para exportação
    const btnPdf = document.getElementById('btn-exportar-pdf');
    if (btnPdf) {
        btnPdf.onclick = function(e) {
            e.preventDefault();
            bloquearExportacao(true);
            showExportFeedback('PDF', 'sucesso', '');
            window.open(`api/caixas/relatorio_pdf.php?caixa_id=${window.CAIXA_ID}`, '_blank');
            setTimeout(() => bloquearExportacao(false), 2000);
        };
    }
    
    const btnExcel = document.getElementById('btn-exportar-excel');
    if (btnExcel) {
        btnExcel.onclick = function(e) {
            e.preventDefault();
            bloquearExportacao(true);
            showExportFeedback('Excel', 'sucesso', '');
            window.open(`api/caixas/relatorio_excel.php?caixa_id=${window.CAIXA_ID}`, '_blank');
            setTimeout(() => bloquearExportacao(false), 2000);
        };
    }
    
    // Carregar dados iniciais
    carregarDadosFechamento();
});

// Listener para navegação mobile
document.querySelectorAll('#side-menu .menu-link').forEach(link => {
    link.addEventListener('click', function() {
        setTimeout(carregarDadosFechamento, 100);
    });
});

// Atualizar tempo aberto a cada minuto
setInterval(() => {
    const tabFechamento = document.getElementById('tab-content-fechamento');
    if (tabFechamento && tabFechamento.classList.contains('active')) {
        carregarStatusCaixa();
    }
}, 60000); // Atualiza a cada minuto 

// Botão expandir jogadores ativos
(function(){
    const btnExpandir = document.getElementById('btn-expandir-jogadores');
    const lista = document.getElementById('jogadores-lista');
    if (btnExpandir && lista) {
        btnExpandir.addEventListener('click', function() {
            if (lista.style.display === 'none' || lista.style.display === '') {
                lista.style.display = 'block';
                btnExpandir.textContent = 'Ocultar lista';
            } else {
                lista.style.display = 'none';
                btnExpandir.textContent = 'Expandir lista';
            }
        });
    }
})(); 