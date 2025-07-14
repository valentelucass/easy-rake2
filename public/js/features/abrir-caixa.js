// JS dedicado para Abrir Novo Caixa
// Responsável por: adicionar/remover linhas, calcular total, listeners

document.addEventListener('DOMContentLoaded', function() {
    const fichasLista = document.getElementById('fichas-lista');
    const btnAdicionarFicha = document.getElementById('btn-adicionar-ficha');
    const totalAberturaEl = document.getElementById('total-abertura');
    const form = document.getElementById('inventario-fichas-form');
    const sucessoContainer = document.getElementById('caixa-aberto-sucesso');
    let isLoading = false;

    function criarLinhaFicha(valor = '', qtd = '') {
        const linha = document.createElement('div');
        linha.className = 'form-group ficha-linha';
        linha.innerHTML = `
            <input type="number" min="0" step="0.01" class="ficha-valor" placeholder="Valor da Ficha" value="${valor}">
            <input type="number" min="0" step="1" class="ficha-qtd" placeholder="Quantidade" value="${qtd}">
            <button type="button" class="btn-remove-ficha" title="Remover"></button>
        `;
        linha.querySelector('.btn-remove-ficha').onclick = () => {
            linha.remove();
            atualizarTotal();
        };
        linha.querySelectorAll('input').forEach(inp => {
            inp.oninput = atualizarTotal;
        });
        return linha;
    }

    function adicionarLinhaFicha() {
        fichasLista.appendChild(criarLinhaFicha());
    }

    function atualizarTotal() {
        let total = 0;
        fichasLista.querySelectorAll('.ficha-linha').forEach(linha => {
            const valor = parseFloat(linha.querySelector('.ficha-valor').value.replace(',', '.')) || 0;
            const qtd = parseInt(linha.querySelector('.ficha-qtd').value) || 0;
            total += valor * qtd;
        });
        totalAberturaEl.textContent = 'Total de Abertura: R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    // Inicialização
    function inicializarFichas() {
        fichasLista.innerHTML = '';
        fichasLista.appendChild(criarLinhaFicha());
        atualizarTotal();
    }

    // Listener do botão adicionar
    if (btnAdicionarFicha) {
        btnAdicionarFicha.onclick = adicionarLinhaFicha;
    }

    // Atualiza total ao digitar
    fichasLista.oninput = atualizarTotal;

    inicializarFichas();

    // --- SUBMISSÃO ROBUSTA DO FORMULÁRIO ---
    if (form) {
        form.onsubmit = async function(e) {
            e.preventDefault();
            if (isLoading) return;
            // Validação básica
            let valido = true;
            let fichas = [];
            let total = 0;
            fichasLista.querySelectorAll('.ficha-linha').forEach(linha => {
                const valor = parseFloat(linha.querySelector('.ficha-valor').value.replace(',', '.')) || 0;
                const qtd = parseInt(linha.querySelector('.ficha-qtd').value) || 0;
                if (valor <= 0 || qtd <= 0) valido = false;
                fichas.push({ valor, qtd });
                total += valor * qtd;
            });
            if (!valido || fichas.length === 0 || total <= 0) {
                alert('Preencha todos os valores e quantidades corretamente.');
                return;
            }
            // Desabilita botões
            isLoading = true;
            form.querySelectorAll('button').forEach(btn => btn.disabled = true);
            sucessoContainer.style.display = 'none';
            sucessoContainer.innerHTML = '';
            // Mostra loading
            totalAberturaEl.textContent = 'Processando abertura...';
            // Envia para o backend
            try {
                const payload = { fichas, valor_inicial: total };
                console.log('Enviando para o backend:', payload);
                const response = await fetch('../src/api/caixas/abrir.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                console.log('Resposta do backend:', result);
                if (result.success && result.caixa_id) {
                    // Mostra card de acesso ao dashboard do caixa aberto
                    sucessoContainer.innerHTML = `
                        <div class="caixa-aberto-sucesso-card" onclick="window.location.href='caixa-aberto-dashboard.php?id=${result.caixa_id}'" style="cursor:pointer;">
                            <div style="font-size:1.15em; font-weight:700; margin-bottom:0.5em;">Caixa aberto com sucesso!</div>
                            <div style="margin-bottom:0.7em;">Valor: <strong>R$ ${Number(result.valor_inicial).toLocaleString('pt-BR', {minimumFractionDigits:2})}</strong></div>
                            <div style="font-size:1.05em; font-weight:500;">Clique aqui para acessar o dashboard do caixa</div>
                        </div>
                    `;
                    sucessoContainer.style.display = 'block';
                    totalAberturaEl.textContent = 'Total de Abertura: R$ ' + Number(result.valor_inicial).toLocaleString('pt-BR', {minimumFractionDigits:2});
                    // Opcional: resetar formulário
                    // form.reset();
                } else {
                    alert('Erro ao abrir caixa.\nMensagem: ' + (result.message || 'Erro desconhecido') + '\nValor enviado: ' + total);
                    totalAberturaEl.textContent = 'Total de Abertura: R$ 0,00';
                }
            } catch (err) {
                alert('Erro de conexão. Tente novamente. Valor enviado: ' + total);
                totalAberturaEl.textContent = 'Total de Abertura: R$ 0,00';
            }
            // Reabilita botões
            isLoading = false;
            form.querySelectorAll('button').forEach(btn => btn.disabled = false);
        }
    }

    // --- CAIXAS ABERTOS ---
    async function carregarCaixasAbertos() {
        const tabela = document.getElementById('tabela-caixas-abertos').querySelector('tbody');
        tabela.innerHTML = '<tr><td colspan="6">Carregando...</td></tr>';
        try {
            const resp = await fetch('api/caixas/listar_abertos.php');
            const data = await resp.json();
            if (data.success && data.caixas.length > 0) {
                tabela.innerHTML = '';
                data.caixas.forEach(caixa => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${caixa.id}</td>
                        <td>${caixa.operador}</td>
                        <td>R$ ${Number(caixa.valor_inicial).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                        <td>${caixa.data_abertura ? new Date(caixa.data_abertura).toLocaleString('pt-BR') : ''}</td>
                        <td><span class="status-badge status-aberto">ABERTO</span></td>
                        <td>
                            <div class="acoes-caixa-flex">
                                <button class="btn btn-primary btn-sm btn-entrar-caixa" data-id="${caixa.id}">Entrar</button>
                                <button class="btn btn-danger btn-sm btn-encerrar-caixa" data-id="${caixa.id}">Encerrar</button>
                            </div>
                        </td>
                    `;
                    tabela.appendChild(tr);
                });
            } else {
                tabela.innerHTML = '<tr><td colspan="6">Nenhum caixa aberto</td></tr>';
            }
        } catch (e) {
            tabela.innerHTML = '<tr><td colspan="6">Erro ao carregar caixas abertos</td></tr>';
        }
    }

    // Listener para entrar no caixa
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-entrar-caixa')) {
            const id = e.target.getAttribute('data-id');
            window.location.href = 'caixa-aberto-dashboard.php?id=' + id;
        }
    });

    // Listener para encerrar caixa
    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains('btn-encerrar-caixa')) {
            const id = e.target.getAttribute('data-id');
            if (!confirm('Tem certeza que deseja encerrar este caixa?')) return;
            e.target.disabled = true;
            e.target.textContent = 'Encerrando...';
            try {
                const respEncerrar = await fetch('../src/api/caixas/encerrar_caixa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await respEncerrar.json();
                if (data.success) {
                    e.target.closest('tr').remove();
                } else {
                    alert(data.message || 'Erro ao encerrar caixa.');
                    e.target.disabled = false;
                    e.target.textContent = 'Encerrar';
                }
            } catch (err) {
                alert('Erro de conexão ao encerrar caixa.');
                e.target.disabled = false;
                e.target.textContent = 'Encerrar';
            }
        }
    });

    window.carregarCaixasAbertos = carregarCaixasAbertos;
    carregarCaixasAbertos();
}); 