// JS da aba Rake - Easy Rake
// Estrutura inicial para integração com API

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-adicionar-rake');
    const valorInput = document.getElementById('valor-rake');
    const errorMsg = document.getElementById('rake-error-msg');
    const tabelaBody = document.querySelector('#tabela-rake-registros tbody');
    const totalEl = document.getElementById('rake-total');
    const btnRelatorio = document.getElementById('btn-gerar-relatorio-rake');

    let registros = [];
    let total = 0;
    let usuarioPerfil = window.PERFIL_USUARIO || '';
    let usuarioNome = window.NOME_USUARIO || '';
    let caixaId = window.CAIXA_ID || null;

    // Exibe ou esconde o botão de relatório conforme perfil
    function atualizarPermissaoRelatorio() {
        if (usuarioPerfil && usuarioPerfil.toLowerCase() === 'gestor') {
            btnRelatorio.style.display = 'inline-block';
        } else {
            btnRelatorio.style.display = 'none';
        }
    }

    // Renderiza a lista de registros e o total
    function renderizarTabela() {
        tabelaBody.innerHTML = '';
        total = 0;
        registros.forEach(reg => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>R$ ${Number(reg.valor).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                <td>${reg.data_hora_formatada}</td>
                <td>${reg.usuario_nome}</td>
            `;
            tabelaBody.appendChild(tr);
            total += parseFloat(reg.valor);
        });
        totalEl.textContent = 'Total Arrecadado: R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2});
    }

    // Carrega registros do backend
    async function carregarRegistros() {
        if (!caixaId) return;
        try {
            const resp = await fetch(`api/caixas/rake_listar.php?caixa_id=${caixaId}`);
            const data = await resp.json();
            if (data.success) {
                registros = data.registros;
                renderizarTabela();
            }
        } catch (e) {
            // erro silencioso
        }
    }

    // Adiciona rake
    if (form) {
        form.onsubmit = async function(e) {
            e.preventDefault();
            errorMsg.style.display = 'none';
            const valor = parseFloat(valorInput.value.replace(',', '.'));
            if (isNaN(valor) || valor <= 0) {
                errorMsg.textContent = 'Digite um valor válido maior que zero.';
                errorMsg.style.display = 'block';
                return;
            }
            if (!caixaId) return;
            try {
                const resp = await fetch('api/caixas/rake_adicionar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ caixa_id: caixaId, valor })
                });
                const data = await resp.json();
                if (data.success) {
                    valorInput.value = '';
                    await carregarRegistros();
                } else {
                    errorMsg.textContent = data.message || 'Erro ao adicionar rake.';
                    errorMsg.style.display = 'block';
                }
            } catch (e) {
                errorMsg.textContent = 'Erro de comunicação com o servidor.';
                errorMsg.style.display = 'block';
            }
        };
    }

    // Geração de relatório
    btnRelatorio.addEventListener('click', function() {
        if (!caixaId) return;
        window.open(`api/caixas/rake_relatorio.php?caixa_id=${caixaId}`, '_blank');
    });

    // Inicialização
    atualizarPermissaoRelatorio();
    carregarRegistros();
}); 