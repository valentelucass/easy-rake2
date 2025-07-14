// Sistema de Aprovações - Easy Rake
let todasAprovacoes = [];
let filtroAtual = 'pendentes';

document.addEventListener('DOMContentLoaded', function() {
    carregarTodasAprovacoes();
    setInterval(carregarTodasAprovacoes, 30000);
    document.getElementById('btn-ver-pendentes').addEventListener('click', () => filtrarTabela('pendentes'));
    document.getElementById('btn-ver-aprovados').addEventListener('click', () => filtrarTabela('aprovados'));
    document.getElementById('btn-ver-rejeitados').addEventListener('click', () => filtrarTabela('rejeitados'));
});

async function carregarTodasAprovacoes() {
    // Carrega pendentes
    let pendentes = [];
    let aprovados = [];
    let rejeitados = [];
    try {
        const respPend = await fetch('../src/api/aprovacoes/listar_pendentes.php');
        const dataPend = await respPend.json();
        if (dataPend.success) {
            pendentes = dataPend.data.itens;
        }
    } catch {}
    try {
        const respHist = await fetch('../src/api/aprovacoes/listar_historico.php');
        const dataHist = await respHist.json();
        if (dataHist.success) {
            aprovados = dataHist.data.filter(a => a.status === 'Aprovado');
            rejeitados = dataHist.data.filter(a => a.status === 'Rejeitado');
        }
    } catch {}
    // Atualiza contadores
    document.getElementById('count-pendentes').textContent = `${pendentes.length} pendentes`;
    document.getElementById('count-aprovados').textContent = `${aprovados.length} aprovados`;
    document.getElementById('count-rejeitados').textContent = `${rejeitados.length} rejeitados`;
    // Salva tudo para filtro
    todasAprovacoes = { pendentes, aprovados, rejeitados };
    filtrarTabela(filtroAtual, true);
}

function filtrarTabela(tipo, manterFiltro) {
    filtroAtual = tipo;
    let lista = [];
    let titulo = '';
    let mostrarAcoes = false;
    if (tipo === 'pendentes') {
        lista = todasAprovacoes.pendentes || [];
        titulo = 'Pendentes';
        mostrarAcoes = true;
    } else if (tipo === 'aprovados') {
        lista = todasAprovacoes.aprovados || [];
        titulo = 'Aprovados';
    } else {
        lista = todasAprovacoes.rejeitados || [];
        titulo = 'Rejeitados';
    }
    document.getElementById('titulo-tabela-aprovacoes').textContent = titulo;
    // Esconde/mostra coluna de ações
    const thAcoes = document.getElementById('th-acoes');
    if (mostrarAcoes) thAcoes.classList.remove('hide-acoes');
    else thAcoes.classList.add('hide-acoes');
    // Monta tabela
    const tbody = document.getElementById('tabela-aprovacoes-tbody');
    tbody.innerHTML = '';
    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="7">Nenhuma aprovação encontrada</td></tr>`;
        return;
    }
    lista.forEach(aprov => {
        let statusClass = 'badge-pendente';
        if (aprov.status === 'Aprovado') statusClass = 'badge-aprovado';
        else if (aprov.status === 'Rejeitado') statusClass = 'badge-rejeitado';
        let acoesTd = '';
        if (mostrarAcoes) {
            acoesTd = `<div class="acoes-aprovacao">
                <button class="button button--small button--success" onclick="aprovarAprovacao('${aprov.tipo}','${aprov.id}')">
                    <i class="icon-check"></i> Aprovar
                </button>
                <button class="button button--small button--danger" onclick="rejeitarAprovacao('${aprov.tipo}','${aprov.id}')">
                    <i class="icon-x"></i> Rejeitar
                </button>
            </div>`;
        }
        tbody.innerHTML += `
            <tr>
                <td>${aprov.id}</td>
                <td><div class="tipo-aprovacao"><span class="tipo-badge tipo-${aprov.tipo.toLowerCase()}">${aprov.tipo}</span></div></td>
                <td><div class="solicitante-info"><strong>${aprov.solicitante}</strong></div></td>
                <td>${aprov.cpf}</td>
                <td>${formatarData(aprov.data_aprovacao || aprov.data_solicitacao)}</td>
                <td><span class="badge ${statusClass}">${aprov.status}</span></td>
                <td class="${mostrarAcoes ? '' : 'hide-acoes'}">${acoesTd}</td>
            </tr>
        `;
    });
}

// Funções globais para aprovação/rejeição
window.aprovarAprovacao = async function(tipo, id) {
    if (!confirm('Tem certeza que deseja aprovar esta solicitação?')) return;
    await acaoAprovacao(tipo, id, 'aprovar');
}

window.rejeitarAprovacao = async function(tipo, id) {
    if (!confirm('Tem certeza que deseja rejeitar esta solicitação?')) return;
    await acaoAprovacao(tipo, id, 'rejeitar');
}

async function acaoAprovacao(tipo, id, acao) {
    const tabela = document.querySelector('.data-table tbody');
    try {
        const resp = await fetch('../src/api/aprovacoes/acao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo, id, acao })
        });
        const data = await resp.json();
        
        if (data.success) {
            showNotification('Sucesso!', data.message, 'success');
            setTimeout(() => {
                carregarTodasAprovacoes(); // Atualiza a lista completa
            }, 1000);
        } else {
            showNotification('Erro!', data.message || 'Não foi possível realizar a ação.', 'error');
        }
    } catch (e) {
        showNotification('Erro!', 'Erro de comunicação com o servidor.', 'error');
    }
}

// Função para exibir histórico
function exibirHistorico() {
    const tabela = document.querySelector('.data-table tbody');
    if (!tabela) return;
    
    tabela.innerHTML = '<tr><td colspan="7">Carregando histórico...</td></tr>';
    
    fetch('../src/api/aprovacoes/listar_historico.php')
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                tabela.innerHTML = `<tr><td colspan="7">${data.message || 'Erro ao buscar histórico.'}</td></tr>`;
                return;
            }
            
            if (!data.data.length) {
                tabela.innerHTML = '<tr><td colspan="7">Nenhum histórico encontrado</td></tr>';
                return;
            }
            
            tabela.innerHTML = '';
            data.data.forEach(item => {
                let statusClass = 'badge-pendente';
                if (item.status === 'Aprovado') statusClass = 'badge-aprovado';
                else if (item.status === 'Rejeitado') statusClass = 'badge-rejeitado';
                else if (item.status === 'Removido') statusClass = 'badge-removido';
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td>
                        <div class="tipo-aprovacao">
                            <span class="tipo-badge tipo-${item.tipo.toLowerCase()}">${item.tipo}</span>
                        </div>
                    </td>
                    <td>
                        <div class="solicitante-info">
                            <strong>${item.solicitante_nome}</strong>
                            <small>${item.tipo_usuario ? '(' + item.tipo_usuario + ')' : ''}</small>
                        </div>
                    </td>
                    <td>
                        <div class="descricao-aprovacao">
                            <div class="descricao-principal">${item.descricao || 'Solicitação processada'}</div>
                            ${item.detalhes ? `<div class="descricao-detalhes">${item.detalhes}</div>` : ''}
                        </div>
                    </td>
                    <td>${formatarData(item.data_aprovacao || item.data_solicitacao)}</td>
                    <td><span class="badge ${statusClass}">${item.status}</span></td>
                    <td>
                        <div class="aprovador-info">
                            <small>Aprovado por: ${item.aprovador_nome || 'Sistema'}</small>
                        </div>
                    </td>
                `;
                tabela.appendChild(tr);
            });
        })
        .catch(() => {
            tabela.innerHTML = '<tr><td colspan="7">Erro ao buscar histórico.</td></tr>';
        });
}

// Event listeners para os botões dos cards
document.addEventListener('DOMContentLoaded', function() {
    // Botão "Ver Todas" (aprovacoes pendentes)
    const btnVerTodas = document.querySelector('.approval-card:nth-child(1) .button--primary');
    if (btnVerTodas) {
        btnVerTodas.addEventListener('click', () => {
            carregarTodasAprovacoes(); // Volta para a lista de pendentes
        });
    }
    
    // Botões "Ver Histórico"
    document.querySelectorAll('.approval-card .button--secondary').forEach(btn => {
        btn.addEventListener('click', exibirHistorico);
    });
});

// Função auxiliar para formatar data
function formatarData(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Função para mostrar notificações (se não existir)
function showNotification(title, message, type = 'info') {
    if (typeof window.showNotification === 'function') {
        window.showNotification(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
} 