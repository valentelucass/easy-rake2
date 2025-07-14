// Aprovações de Acesso - Easy Rake

document.addEventListener('DOMContentLoaded', async function() {
    try {
        const resp = await fetch('api/unidades/get_info_gestor.php', { credentials: 'include' });
        if (!resp.ok) {
            document.getElementById('info-gestor-unidade').style.display = 'none';
            alert('Sessão expirada ou erro de autenticação. Você será redirecionado para o login.');
            setTimeout(() => { window.location.href = '/easy-rake/public/index.php'; }, 2000);
            return;
        }
        const data = await resp.json();
        if (data.success && data.data) {
            const info = data.data;
            document.getElementById('gestor-nome').textContent = info.gestor_nome || window.NOME_GESTOR || 'Gestor';
            document.getElementById('unidade-nome').textContent = info.nome;
            document.getElementById('codigo-acesso').textContent = info.codigo_acesso;
            document.getElementById('gestor-email').textContent = info.gestor_email || '-';
            document.getElementById('unidade-status').textContent = info.status_texto || info.status;
            document.getElementById('unidade-telefone').textContent = info.telefone || '-';
            document.getElementById('unidade-data-criacao').textContent = info.data_criacao_formatada || '-';
            document.getElementById('info-gestor-unidade').style.display = 'block';
        }
    } catch (e) {
        // Oculta bloco se erro
        document.getElementById('info-gestor-unidade').style.display = 'none';
    }
    carregarPendentes();
    carregarHistorico();
});

async function carregarPendentes() {
    const tbody = document.getElementById('tabela-aprovacoes-pendentes');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7">Carregando...</td></tr>';
    try {
        const resp = await fetch('api/aprovacoes_acesso/listar_pendentes.php');
        const result = await resp.json();
        const data = result.data && result.data.itens ? result.data.itens : [];
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="7">Nenhuma aprovação pendente</td></tr>';
            return;
        }
        tbody.innerHTML = '';
        data.forEach(aprov => {
            let statusClass = 'badge-pendente';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${aprov.id}</td>
                <td>${aprov.tipo}</td>
                <td>${aprov.solicitante}</td>
                <td>${aprov.cpf}</td>
                <td>${formatarData(aprov.data_solicitacao)}</td>
                <td><span class="badge ${statusClass}">${aprov.status}</span></td>
                <td>
                    <button class="button button--small button--success" onclick="aprovarAcesso(${aprov.id})">Aprovar</button>
                    <button class="button button--small button--danger" onclick="rejeitarAcesso(${aprov.id})">Rejeitar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7">Erro ao buscar aprovações.</td></tr>';
    }
}

async function carregarHistorico() {
    const tbody = document.getElementById('tabela-aprovacoes-historico');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7">Carregando...</td></tr>';
    try {
        const resp = await fetch('api/aprovacoes_acesso/listar_historico.php');
        const result = await resp.json();
        const data = Array.isArray(result.data) ? result.data : [];
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="7">Nenhum histórico encontrado</td></tr>';
            return;
        }
        tbody.innerHTML = '';
        data.forEach(item => {
            let statusClass = item.status === 'Aprovado' ? 'badge-aprovado' : 'badge-rejeitado';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id}</td>
                <td>${item.tipo}</td>
                <td>${item.solicitante}</td>
                <td>${item.cpf}</td>
                <td>${formatarData(item.data_solicitacao)}</td>
                <td><span class="badge ${statusClass}">${item.status}</span></td>
                <td>${formatarData(item.data_decisao)}</td>
                <td>${item.gestor_nome || '-'}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7">Erro ao buscar histórico.</td></tr>';
    }
}

window.aprovarAcesso = async function(id) {
    await acaoAcesso(id, 'aprovar');
}
window.rejeitarAcesso = async function(id) {
    await acaoAcesso(id, 'rejeitar');
}
async function acaoAcesso(id, acao) {
    try {
        const respAcao = await fetch('../src/api/aprovacoes_acesso/acao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, acao })
        });
        const data = await respAcao.json();
        if (data.success) {
            carregarPendentes();
            carregarHistorico();
        } else {
            alert(data.message || 'Erro ao processar solicitação.');
        }
    } catch (e) {
        alert('Erro de comunicação com o servidor.');
    }
}
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
window.copiarCodigoAcesso = function() {
    const codigoElement = document.getElementById('codigo-acesso');
    const button = document.querySelector('.btn-copiar-codigo');
    if (codigoElement) {
        const codigo = codigoElement.textContent;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(codigo).then(function() {
                feedbackCopiado(button);
            }).catch(function() {
                fallbackCopy(codigo, button);
            });
        } else {
            fallbackCopy(codigo, button);
        }
    }
}
function feedbackCopiado(button) {
    if (!button) return;
    const originalText = button.textContent;
    button.textContent = 'Copiado!';
    button.style.background = '#28a745';
    setTimeout(() => {
        button.textContent = originalText;
        button.style.background = '';
    }, 1800);
}
function fallbackCopy(text, button) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand('copy');
        feedbackCopiado(button);
    } catch (err) {
        alert('Erro ao copiar código. Tente selecionar e copiar manualmente.');
    }
    document.body.removeChild(tempInput);
} 