// JS para a aba Transações de Fichas

document.addEventListener('DOMContentLoaded', function() {
    // Elementos principais
    const buscaInput = document.getElementById('fichas-busca-input');
    const buscaBtn = document.getElementById('fichas-busca-btn');
    const listaResultados = document.getElementById('fichas-busca-resultados');
    const dadosJogadorBox = document.getElementById('fichas-dados-jogador');
    const formTransacao = document.getElementById('fichas-form-transacao');
    const valorInput = document.getElementById('fichas-valor');
    const btnVender = document.getElementById('fichas-btn-vender');
    const btnDevolver = document.getElementById('fichas-btn-devolver');
    const alertaCredito = document.getElementById('fichas-alerta-credito');
    const tabelaJogadores = document.getElementById('fichas-tabela-jogadores');
    const btnImprimirTodas = document.getElementById('fichas-btn-imprimir-todas');
    // ... outros elementos/modais

    let jogadorSelecionado = null;
    let podeProsseguirCredito = false;

    // Busca de jogador
    if (buscaBtn && buscaInput) {
        buscaBtn.onclick = function(e) {
            e.preventDefault();
            buscarJogadores(buscaInput.value.trim());
        };
        buscaInput.onkeyup = function(e) {
            if (e.key === 'Enter') buscarJogadores(buscaInput.value.trim());
        };
    }

    function buscarJogadores(termo) {
        if (!termo) return;
        fetch('../src/api/jogadores/buscar_jogadores.php?busca=' + encodeURIComponent(termo))
            .then(r => r.json())
            .then(data => {
                if (data.success && data.jogadores.length > 0) {
                    mostrarResultadosBusca(data.jogadores);
                } else {
                    listaResultados.innerHTML = '<div class="fichas-busca-nenhum">Nenhum jogador encontrado.</div>';
                }
            });
    }

    function mostrarResultadosBusca(jogadores) {
        listaResultados.innerHTML = '';
        jogadores.forEach(jogador => {
            const div = document.createElement('div');
            div.className = 'fichas-busca-resultado';
            div.textContent = `${jogador.nome} - ${jogador.cpf} - ${jogador.telefone}`;
            div.onclick = () => selecionarJogador(jogador);
            listaResultados.appendChild(div);
        });
    }

    // Mostrar/ocultar boxes ao selecionar jogador
    function selecionarJogador(jogador) {
        jogadorSelecionado = jogador;
        podeProsseguirCredito = false;
        // Preencher dados do jogador
        dadosJogadorBox.innerHTML = `
            <div class="fichas-dado"><strong>Nome:</strong> ${jogador.nome}</div>
            <div class="fichas-dado"><strong>CPF:</strong> ${jogador.cpf}</div>
            <div class="fichas-dado"><strong>Telefone:</strong> ${jogador.telefone}</div>
            <div class="fichas-dado"><strong>Saldo Atual:</strong> R$ ${Number(jogador.saldo_atual).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
            <div class="fichas-dado"><strong>Limite de Crédito:</strong> R$ ${Number(jogador.limite_credito).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
            <div class="fichas-dado"><strong>Situação:</strong> ${jogador.status}</div>
        `;
        document.getElementById('fichas-dados-jogador-box').style.display = 'block';
        document.getElementById('fichas-transacao-box').style.display = 'block';
        dadosJogadorBox.style.display = 'flex';
        formTransacao.style.display = 'flex';
    }

    // Delegação de eventos para botões da tabela
    tabelaJogadores.addEventListener('click', function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const jogadorId = btn.getAttribute('data-id');
        if (btn.classList.contains('fichas-btn--rebuy')) {
            acionarRebuy(jogadorId);
        } else if (btn.classList.contains('fichas-btn--devolucao')) {
            acionarDevolucaoRapida(jogadorId);
        } else if (btn.classList.contains('fichas-btn--detalhes')) {
            abrirModalDetalhes(jogadorId);
        } else if (btn.classList.contains('fichas-btn--imprimir')) {
            imprimirReciboJogador(jogadorId);
        }
    });

    function acionarRebuy(jogadorId) {
        // Mock: preencher valor e acionar venda
        valorInput.value = 100; // valor sugerido, pode ser customizado
        buscarJogadorPorId(jogadorId, () => {
            btnVender.focus();
        });
    }
    function acionarDevolucaoRapida(jogadorId) {
        valorInput.value = 100; // valor sugerido
        buscarJogadorPorId(jogadorId, () => {
            btnDevolver.focus();
        });
    }
    function buscarJogadorPorId(id, cb) {
        fetch('../src/api/jogadores/buscar_jogadores.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.jogadores.length > 0) {
                    selecionarJogador(data.jogadores[0]);
                    if (cb) cb();
                }
            });
    }

    // Modal de detalhes
    const modalDetalhes = document.getElementById('fichas-modal-detalhes');
    const modalConteudo = document.getElementById('fichas-modal-detalhes-conteudo');
    const modalFechar = document.getElementById('fichas-modal-detalhes-close');
    const modalImprimir = document.getElementById('fichas-modal-detalhes-imprimir');

    function abrirModalDetalhes(jogadorId) {
        fetch('../src/api/fichas/historico_jogador.php?id=' + jogadorId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    modalConteudo.innerHTML = gerarTabelaHistorico(data.historico);
                    modalDetalhes.style.display = 'flex';
                    modalImprimir.onclick = function() {
                        imprimirReciboDetalhes(jogadorId);
                    };
                }
            });
    }
    if (modalFechar) {
        modalFechar.onclick = function() {
            modalDetalhes.style.display = 'none';
        };
    }
    function gerarTabelaHistorico(historico) {
        if (!historico || historico.length === 0) return '<div>Nenhuma transação encontrada.</div>';
        let html = '<table class="historico-table"><thead><tr><th>Tipo</th><th>Valor</th><th>Data/Hora</th><th>Operador</th></tr></thead><tbody>';
        historico.forEach(item => {
            html += `<tr><td>${item.tipo}</td><td>R$ ${Number(item.valor).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td><td>${item.data_hora}</td><td>${item.operador}</td></tr>`;
        });
        html += '</tbody></table>';
        return html;
    }

    // Impressão de recibos
    function imprimirReciboJogador(jogadorId) {
        window.open('api/fichas/imprimir_recibo.php?id=' + jogadorId, '_blank');
    }
    function imprimirReciboDetalhes(jogadorId) {
        window.open('api/fichas/imprimir_recibo.php?id=' + jogadorId + '&detalhes=1', '_blank');
    }
    if (btnImprimirTodas) {
        btnImprimirTodas.onclick = function() {
            window.open('api/fichas/imprimir_todas.php', '_blank');
        };
    }

    // Feedback visual (toast simples)
    function mostrarToast(msg, tipo = 'info') {
        let toast = document.createElement('div');
        toast.className = 'toast-msg toast-' + tipo;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 100);
        setTimeout(() => { toast.classList.remove('show'); toast.remove(); }, 3200);
    }
    // Substituir mostrarAlerta para usar toast
    function mostrarAlerta(msg) {
        mostrarToast(msg, 'info');
    }
    function mostrarAlertaCredito() {
        alertaCredito.innerHTML = '⚠ LIMITE DE CRÉDITO EXCEDIDO. RECOMENDA-SE CONSULTAR O GESTOR DO CASH GAME.<br><button id="fichas-btn-prosseguir">Prosseguir</button> <button id="fichas-btn-cancelar">Cancelar</button>';
        alertaCredito.style.display = 'block';
        document.getElementById('fichas-btn-prosseguir').onclick = function() {
            podeProsseguirCredito = true;
            alertaCredito.style.display = 'none';
            formTransacao.requestSubmit();
        };
        document.getElementById('fichas-btn-cancelar').onclick = function() {
            podeProsseguirCredito = false;
            alertaCredito.style.display = 'none';
        };
    }

    function realizarTransacao(valor, tipo) {
        fetch('../src/api/fichas/registrar_transacao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                jogador_id: jogadorSelecionado.id,
                valor: valor,
                tipo: tipo
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Transação realizada com sucesso!');
                valorInput.value = '';
                atualizarTabelaJogadores();
                gerarRecibo(data.recibo);
            } else {
                mostrarAlerta(data.message || 'Erro ao registrar transação.');
            }
        });
    }

    // Atualização da tabela de jogadores ativos
    function atualizarTabelaJogadores() {
        fetch('../src/api/fichas/listar_jogadores_sessao.php')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    preencherTabelaJogadores(data.jogadores);
                }
            });
    }
    function preencherTabelaJogadores(jogadores) {
        const tbody = tabelaJogadores.querySelector('tbody');
        tbody.innerHTML = '';
        jogadores.forEach(jogador => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${jogador.nome}</td>
                <td>${jogador.fichas_compradas}</td>
                <td>${jogador.fichas_devolvidas}</td>
                <td>R$ ${Number(jogador.saldo_atual).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td><button class="fichas-btn fichas-btn--rebuy" data-id="${jogador.id}">Re-buy</button></td>
                <td><button class="fichas-btn fichas-btn--devolucao" data-id="${jogador.id}">Devolução</button></td>
                <td><button class="fichas-btn fichas-btn--detalhes" data-id="${jogador.id}">Detalhes</button></td>
                <td><button class="fichas-btn fichas-btn--imprimir" data-id="${jogador.id}"><span class="icon-impressora"></span></button></td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Geração de recibo (simples, pode ser adaptado para impressão térmica)
    function gerarRecibo(dados) {
        // Aqui você pode abrir um modal ou janela de impressão com o recibo formatado
        // Exemplo: window.open('recibo.php?id='+dados.id, '_blank');
    }

    // Inicialização
    atualizarTabelaJogadores();
}); 