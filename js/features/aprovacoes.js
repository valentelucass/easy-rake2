document.addEventListener('DOMContentLoaded', () => {
    carregarAprovacoes();
    atualizarCardsEstatisticas();
});

async function carregarAprovacoes() {
    const tabela = document.querySelector('.data-table tbody');
    if (!tabela) return;
    tabela.innerHTML = '<tr><td colspan="6">Carregando...</td></tr>';
    try {
        const resp = await fetch('api/aprovacoes_listar_pendentes.php');
        const data = await resp.json();
        if (!data.success) {
            tabela.innerHTML = `<tr><td colspan="6">${data.message || 'Erro ao buscar aprovações.'}</td></tr>`;
            return;
        }
        if (!data.aprovacoes.length) {
            tabela.innerHTML = '<tr><td colspan="6">Nenhuma aprovação pendente encontrada</td></tr>';
            return;
        }
        tabela.innerHTML = '';
        data.aprovacoes.forEach(aprov => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${aprov.id}</td>
                <td>${aprov.tipo}</td>
                <td>${aprov.solicitante_nome || '-'}</td>
                <td>${formatarData(aprov.data_solicitacao)}</td>
                <td><span class="badge badge-pendente">${aprov.status}</span></td>
                <td>
                    <button class="button button--small button--success" onclick="aprovarAprovacao('${aprov.tipo}','${aprov.id}')">Aprovar</button>
                    <button class="button button--small button--danger" onclick="rejeitarAprovacao('${aprov.tipo}','${aprov.id}')">Rejeitar</button>
                </td>
            `;
            tabela.appendChild(tr);
        });
    } catch (e) {
        tabela.innerHTML = '<tr><td colspan="6">Erro ao buscar aprovações.</td></tr>';
    }
}

async function atualizarCardsEstatisticas() {
    // Seletores dos cards
    const pendentesEl = document.querySelector('.approval-card:nth-child(1) .approval-count');
    const aprovadasHojeEl = document.querySelector('.approval-card:nth-child(2) .approval-count');
    const rejeitadasHojeEl = document.querySelector('.approval-card:nth-child(3) .approval-count');
    const totalEl = document.querySelector('.approval-card:nth-child(4) .approval-count');

    // Buscar pendentes
    let pendentes = 0;
    try {
        const respPend = await fetch('api/aprovacoes_listar_pendentes.php');
        const dataPend = await respPend.json();
        if (dataPend.success) {
            pendentes = dataPend.aprovacoes.length;
        }
    } catch {}
    if (pendentesEl) pendentesEl.textContent = `${pendentes} pendentes`;

    // Buscar histórico
    let aprovadasHoje = 0, rejeitadasHoje = 0, totalAprovadas = 0, totalRejeitadas = 0;
    let total = 0;
    try {
        const respHist = await fetch('api/aprovacoes_listar_historico.php');
        const dataHist = await respHist.json();
        if (dataHist.success) {
            const hoje = new Date().toISOString().slice(0,10);
            dataHist.historico.forEach(item => {
                if (item.status === 'Aprovado') {
                    totalAprovadas++;
                    if (item.data_aprovacao && item.data_aprovacao.slice(0,10) === hoje) aprovadasHoje++;
                }
                if (item.status === 'Rejeitado') {
                    totalRejeitadas++;
                    if (item.data_aprovacao && item.data_aprovacao.slice(0,10) === hoje) rejeitadasHoje++;
                }
            });
            total = dataHist.historico.length;
        }
    } catch {}
    if (aprovadasHojeEl) aprovadasHojeEl.textContent = `${aprovadasHoje} aprovadas hoje`;
    if (rejeitadasHojeEl) rejeitadasHojeEl.textContent = `${rejeitadasHoje} rejeitadas hoje`;
    if (totalEl) totalEl.textContent = `${total} no total`;
}

function formatarData(dataStr) {
    if (!dataStr) return '-';
    const d = new Date(dataStr);
    return d.toLocaleString('pt-BR');
}

window.aprovarAprovacao = async function(tipo, id) {
    if (!confirm('Tem certeza que deseja aprovar este cadastro?')) return;
    await acaoAprovacao(tipo, id, 'aprovar');
}

window.rejeitarAprovacao = async function(tipo, id) {
    if (!confirm('Tem certeza que deseja rejeitar este cadastro?')) return;
    await acaoAprovacao(tipo, id, 'rejeitar');
}

async function acaoAprovacao(tipo, id, acao) {
    const tabela = document.querySelector('.data-table tbody');
    try {
        const resp = await fetch('api/aprovacoes_acao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo, id, acao })
        });
        const data = await resp.json();
        if (data.success) {
            alert(data.message);
            carregarAprovacoes();
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível realizar a ação.'));
        }
    } catch (e) {
        alert('Erro de comunicação com o servidor.');
    }
}

// HISTÓRICO
const btnHistorico = document.querySelector('.approval-card .button--secondary, .approval-card .button--primary');
if (btnHistorico) {
    document.querySelectorAll('.approval-card .button--secondary').forEach(btn => {
        btn.addEventListener('click', exibirHistorico);
    });
}

function exibirHistorico() {
    const tabela = document.querySelector('.data-table tbody');
    if (!tabela) return;
    tabela.innerHTML = '<tr><td colspan="6">Carregando histórico...</td></tr>';
    fetch('api/aprovacoes_listar_historico.php')
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                tabela.innerHTML = `<tr><td colspan='6'>${data.message || 'Erro ao buscar histórico.'}</td></tr>`;
                return;
            }
            if (!data.historico.length) {
                tabela.innerHTML = '<tr><td colspan="6">Nenhum histórico encontrado</td></tr>';
                return;
            }
            tabela.innerHTML = '';
            data.historico.forEach(item => {
                let acoes = '-';
                if (item.tipo === 'Sanger' && item.status === 'Aprovado') {
                    acoes = `<button class='button button--small button--danger' onclick="removerSanger('${item.id}')">Remover</button>`;
                }
                let statusBadge = `<span class='badge badge-${item.status.toLowerCase()}'>${item.status}</span>`;
                if (item.status === 'Removido') {
                    statusBadge = `<span class='badge badge-removido' style='background:#444;color:#fff;'>Removido</span>`;
                }
                tabela.innerHTML += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.tipo}</td>
                        <td>${item.solicitante_nome || '-'}</td>
                        <td>${formatarData(item.data_solicitacao)}</td>
                        <td>${statusBadge}</td>
                        <td>${acoes}</td>
                    </tr>
                `;
            });
        })
        .catch(() => {
            tabela.innerHTML = '<tr><td colspan="6">Erro ao buscar histórico.</td></tr>';
        });
}

window.removerSanger = async function(id) {
    if (!confirm('Tem certeza que deseja remover este Sanger da unidade?')) return;
    await acaoAprovacao('Sanger', id, 'remover');
    exibirHistorico();
} 