document.addEventListener('DOMContentLoaded', function() {
    const currencyFormatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });

    function formatCurrency(value) {
        return currencyFormatter.format(value);
    }

    async function loadDashboardData() {
        try {
            const response = await fetch(`../../api/caixas/get_session_stats.php?caixa_id=${CAIXA_ID}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.status === 'success') {
                updateUI(data.data);
            } else {
                console.error('Erro ao buscar dados do dashboard:', data.message);
            }
        } catch (error) {
            console.error('Falha ao carregar dados do dashboard:', error);
        }
    }

    function updateUI(data) {
        // Inventário
        document.getElementById('inventario-abertura').textContent = formatCurrency(data.inventario.abertura);
        document.getElementById('inventario-fichas-vendidas').textContent = formatCurrency(data.inventario.fichas_vendidas);
        document.getElementById('inventario-fichas-devolvidas').textContent = formatCurrency(data.inventario.fichas_devolvidas);
        document.getElementById('inventario-atual-calculado').textContent = formatCurrency(data.inventario.inventario_atual);

        // Receitas
        document.getElementById('receitas-rake').textContent = formatCurrency(data.receitas.rake_total);
        document.getElementById('receitas-cashback').textContent = formatCurrency(data.receitas.cashback_caixinhas);
        document.getElementById('receitas-total').textContent = formatCurrency(data.receitas.receitas_totais);

        // Despesas
        document.getElementById('despesas-total').textContent = formatCurrency(data.despesas.despesas_totais);

        // Saldo Operacional
        document.getElementById('saldo-operacional').textContent = formatCurrency(data.saldo_operacional);

        // Caixinhas
        document.getElementById('caixinhas-bruto').textContent = formatCurrency(data.caixinhas.total_bruto);
        document.getElementById('caixinhas-liquido').textContent = formatCurrency(data.caixinhas.total_liquido);
        const caixinhasDetalhes = document.getElementById('caixinhas-detalhes');
        caixinhasDetalhes.innerHTML = '';
        if (data.caixinhas.detalhes && data.caixinhas.detalhes.length > 0) {
            const list = document.createElement('ul');
            data.caixinhas.detalhes.forEach(c => {
                const item = document.createElement('li');
                item.textContent = `${c.nome}: ${formatCurrency(c.valor_liquido)} (p/ ${c.participantes} part. = ${formatCurrency(c.valor_por_participante)})`;
                list.appendChild(item);
            });
            caixinhasDetalhes.appendChild(list);
        } else {
            caixinhasDetalhes.textContent = 'Nenhuma caixinha registrada.';
        }

        // Jogadores Ativos
        document.getElementById('jogadores-total').textContent = data.jogadores_ativos.total_jogadores_ativos;
        const jogadoresTbody = document.querySelector('#jogadores-table tbody');
        jogadoresTbody.innerHTML = '';
        if (data.jogadores_ativos.lista && data.jogadores_ativos.lista.length > 0) {
            data.jogadores_ativos.lista.forEach(j => {
                const row = jogadoresTbody.insertRow();
                row.innerHTML = `
                    <td>${j.nome}</td>
                    <td>${formatCurrency(j.fichas_compradas)}</td>
                    <td>${formatCurrency(j.fichas_devolvidas)}</td>
                    <td>${formatCurrency(j.saldo_atual)}</td>
                    <td class="situacao-${j.situacao.toLowerCase().replace(' ', '-')}">${j.situacao}</td>
                `;
            });
        } else {
            const row = jogadoresTbody.insertRow();
            row.innerHTML = `<td colspan="5">Nenhum jogador ativo na sessão.</td>`;
        }
    }

    // Carrega os dados quando a aba se torna ativa e depois a cada 30 segundos
    // A lógica de verificação de aba ativa foi removida, pois este script agora é carregado apenas na página de fechamento.
    loadDashboardData();
    setInterval(loadDashboardData, 30000);

    // Manter a lógica de encerramento de caixa e outras funcionalidades da página de fechamento
    const btnEncerrarCaixa = document.getElementById('btn-encerrar-caixa');
    if(btnEncerrarCaixa) {
        btnEncerrarCaixa.addEventListener('click', encerrarCaixa);
    }

    function encerrarCaixa() {
        if (!confirm('Tem certeza que deseja encerrar o caixa? Esta ação não pode ser desfeita.')) {
            return;
        }

        fetch(`../../api/caixas/encerrar_caixa.php?caixa_id=${CAIXA_ID}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Caixa encerrado com sucesso!');
                    window.location.href = 'caixas.php'; // Redireciona para a lista de caixas
                } else {
                    alert(`Erro ao encerrar o caixa: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro na requisição para encerrar o caixa.');
            });
    }

    // Manter outras funções se necessário, como exportação
    const btnExportarPdf = document.getElementById('btn-exportar-pdf');
    const btnExportarExcel = document.getElementById('btn-exportar-excel');

    if(btnExportarPdf) {
        btnExportarPdf.addEventListener('click', () => exportarRelatorio('pdf'));
    }
    if(btnExportarExcel) {
        btnExportarExcel.addEventListener('click', () => exportarRelatorio('excel'));
    }

    function exportarRelatorio(formato) {
        alert(`Funcionalidade de exportar para ${formato.toUpperCase()} ainda não implementada.`);
    }

});
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