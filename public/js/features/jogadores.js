// JS específico para a página de jogadores

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    const btnsEditar = document.querySelectorAll('.btn-editar-jogador');
    const btnsExcluir = document.querySelectorAll('.btn-excluir-jogador');

    // Buscar jogadores
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            buscarJogadores();
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            if (searchInput.value.length === 0 || searchInput.value.length > 2) {
                buscarJogadores();
            }
        });
    }

    // Função para atualizar tabela de jogadores dinamicamente
    function atualizarTabelaJogadores(jogadores) {
        const tbody = document.querySelector('#tabela-jogadores tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!jogadores || jogadores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8">Nenhum jogador encontrado</td></tr>';
            return;
        }
        jogadores.forEach(jogador => {
            const saldo = parseFloat(jogador.saldo_atual);
            const limite = parseFloat(jogador.limite_credito);
            let situacao = 'Em dia';
            if (saldo < 0) {
                situacao = 'Devedor';
                if (limite > 0 && Math.abs(saldo) > limite) {
                    situacao = 'Limite Excedido';
                }
            } else if (saldo > 0) {
                situacao = 'A receber';
            }
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${jogador.nome}</td>
                <td>${jogador.cpf}</td>
                <td>${jogador.telefone}</td>
                <td>R$ ${Number(limite).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td class="saldo-cell" style="color:${saldo < 0 ? 'red' : (saldo > 0 ? 'green' : 'inherit')}">R$ ${Number(saldo).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td>${jogador.data_cadastro}</td>
                <td><span class="status-badge status-${situacao.toLowerCase().replace(' ', '-')}">${situacao}</span></td>
                <td>
                    <button class="button button--small btn-quitar-saldo" data-id="${jogador.id}" data-nome="${jogador.nome}" data-saldo="${saldo}">Quitar</button>
                    <button class="button button--small btn-editar-jogador" data-id="${jogador.id}">Editar</button>
                    <button class="button button--small button--danger btn-excluir-jogador" data-id="${jogador.id}">Excluir</button>
                </td>
            `;
            tbody.appendChild(row);
        });
        // Reatribuir eventos aos novos botões
        atribuirEventosAosBotoes();
    }

    function buscarJogadores() {
        const termo = searchInput.value.trim();
        fetch('../src/api/jogadores/buscar_jogadores.php?busca=' + encodeURIComponent(termo))
            .then(r => r.json())
            .then(data => {
                console.log('Resposta buscarJogadores:', data); // Log de depuração
                if (data.success) {
                    atualizarTabelaJogadores(data.jogadores);
                } else {
                    atualizarTabelaJogadores([]);
                }
            })
            .catch((err) => {
                console.error('Erro ao buscar jogadores:', err);
                atualizarTabelaJogadores([]);
            });
    }

    function abrirModalEdicaoJogador(jogadorId) {
        fetch('../src/api/jogadores/buscar_jogadores.php?id=' + jogadorId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.jogador) {
                    document.getElementById('modal-jogador-titulo').innerText = 'Editar Jogador';
                    document.getElementById('jogador-nome').value = data.jogador.nome;
                    document.getElementById('jogador-cpf').value = data.jogador.cpf;
                    document.getElementById('jogador-telefone').value = data.jogador.telefone;
                    document.getElementById('jogador-limite').value = data.jogador.limite_credito;
                    msgModalJogador.innerText = '';
                    modalJogador.style.display = 'flex';
                    formJogador.setAttribute('data-edit-id', jogadorId);
                    document.getElementById('jogador-cpf').disabled = false;
                } else {
                    alert('Erro ao buscar dados do jogador.');
                }
            })
            .catch(() => alert('Erro ao buscar dados do jogador.'));
    }

    // Substituir evento de editar
    function atribuirEventosAosBotoes() {
        document.querySelectorAll('.btn-editar-jogador').forEach(btn => {
            btn.onclick = function() {
                const jogadorId = btn.dataset.id;
                abrirModalEdicaoJogador(jogadorId);
            };
        });
        document.querySelectorAll('.btn-excluir-jogador').forEach(btn => {
            btn.onclick = function() {
                const jogadorId = btn.dataset.id;
                if (confirm('Tem certeza que deseja excluir este jogador?')) {
                    fetch('../src/api/jogadores/excluir_jogador.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: jogadorId })
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log('Resposta excluirJogador:', data); // Log de depuração
                        if (data.success) buscarJogadores();
                        else alert('Erro ao excluir jogador!');
                    })
                    .catch((err) => {
                        console.error('Erro na exclusão:', err);
                        alert('Erro ao excluir jogador!');
                    });
                }
            };
        });
    }

    // Modal de cadastro de jogador
    const btnNovoJogador = document.getElementById('btn-novo-jogador');
    const modalJogador = document.getElementById('modal-jogador');
    const fecharModalJogador = document.getElementById('fechar-modal-jogador');
    const formJogador = document.getElementById('form-jogador');
    const msgModalJogador = document.getElementById('modal-jogador-msg');

    if(btnNovoJogador && modalJogador && fecharModalJogador && formJogador) {
        btnNovoJogador.onclick = () => {
            document.getElementById('modal-jogador-titulo').innerText = 'Novo Jogador';
            formJogador.reset();
            msgModalJogador.innerText = '';
            modalJogador.style.display = 'flex';
            formJogador.removeAttribute('data-edit-id');
            document.getElementById('jogador-cpf').disabled = false;
        };
        fecharModalJogador.onclick = () => {
            modalJogador.style.display = 'none';
        };
        window.onclick = function(event) {
            if (event.target === modalJogador) modalJogador.style.display = 'none';
        };
        formJogador.onsubmit = function(e) {
            e.preventDefault();
            msgModalJogador.innerText = '';
            const dados = {
                nome: document.getElementById('jogador-nome').value.trim(),
                cpf: document.getElementById('jogador-cpf').value.trim(),
                telefone: document.getElementById('jogador-telefone').value.trim(),
                limite_credito: parseFloat(document.getElementById('jogador-limite').value) || 0
            };
            const editId = formJogador.getAttribute('data-edit-id');
            let url = '../src/api/jogadores/criar.php';
            let method = 'POST';
            if (editId) {
                url = 'api/jogadores/buscar_jogadores.php?id=' + editId;
                method = 'PUT';
            }
            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(r => r.json())
            .then(data => {
                console.log('Resposta registrarJogador:', data); // Log de depuração
                if(data.success) {
                    msgModalJogador.style.color = 'green';
                    msgModalJogador.innerText = editId ? 'Jogador atualizado com sucesso!' : 'Jogador cadastrado com sucesso!';
                    setTimeout(() => { 
                        modalJogador.style.display = 'none'; 
                        buscarJogadores(); // Atualiza a tabela dinamicamente
                    }, 1000);
                } else {
                    msgModalJogador.style.color = 'red';
                    msgModalJogador.innerText = data.message || (editId ? 'Erro ao atualizar jogador.' : 'Erro ao cadastrar jogador.');
                }
            })
            .catch((err) => {
                console.error('Erro no cadastro:', err);
                msgModalJogador.style.color = 'red';
                msgModalJogador.innerText = editId ? 'Erro ao atualizar jogador.' : 'Erro ao cadastrar jogador.';
            });
        };
    } else {
        if(!btnNovoJogador) console.error('Botão Novo Jogador não encontrado no DOM!');
    }

    // Máscara automática de CPF
    const cpfInput = document.getElementById('jogador-cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            // Remove tudo que não for número
            let v = cpfInput.value.replace(/\D/g, '');
            // Limita a 11 dígitos
            v = v.slice(0, 11);
            cpfInput.value = v;
        });
        cpfInput.addEventListener('blur', function(e) {
            let v = cpfInput.value.replace(/\D/g, '');
            if (v.length === 11) {
                cpfInput.value = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
        });
    }

    // Inicializa eventos nos botões já renderizados
    atribuirEventosAosBotoes();

    // Modal de quitação de saldo
    const modalQuitar = document.getElementById('modal-quitar-saldo');
    const fecharModalQuitar = document.getElementById('fechar-modal-quitar');
    const formQuitar = document.getElementById('form-quitar-saldo');
    const msgModalQuitar = document.getElementById('modal-quitar-msg');
    const quitarValor = document.getElementById('quitar-valor');
    const quitarSaldoFinal = document.getElementById('quitar-saldo-final');
    const quitarDebito = document.getElementById('quitar-debito');
    const quitarCredito = document.getElementById('quitar-credito');

    // Eventos para botões de quitar
    function atribuirEventosQuitar() {
        document.querySelectorAll('.btn-quitar-saldo').forEach(btn => {
            btn.onclick = function() {
                const jogadorId = btn.dataset.id;
                const jogadorNome = btn.dataset.nome;
                const saldoAtual = parseFloat(btn.dataset.saldo);
                
                document.getElementById('quitar-jogador-id').value = jogadorId;
                document.getElementById('quitar-jogador-nome').textContent = jogadorNome;
                document.getElementById('quitar-saldo-atual').textContent = `R$ ${saldoAtual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                document.getElementById('quitar-saldo-valor').value = saldoAtual;
                
                // Limpa formulário
                formQuitar.reset();
                quitarValor.value = '';
                quitarSaldoFinal.textContent = '';
                msgModalQuitar.textContent = '';
                
                // Habilita/desabilita opções baseado no saldo
                if (saldoAtual < 0) {
                    quitarDebito.disabled = false;
                    quitarCredito.disabled = true;
                    quitarDebito.checked = true;
                } else if (saldoAtual > 0) {
                    quitarDebito.disabled = true;
                    quitarCredito.disabled = false;
                    quitarCredito.checked = true;
                } else {
                    quitarDebito.disabled = true;
                    quitarCredito.disabled = true;
                    alert('Jogador não possui saldo para quitar.');
                    return;
                }
                
                modalQuitar.style.display = 'flex';
            };
        });
    }

    // Fechar modal de quitação
    if (fecharModalQuitar) {
        fecharModalQuitar.onclick = () => {
            modalQuitar.style.display = 'none';
        };
    }

    // Calcular saldo final em tempo real
    function calcularSaldoFinal() {
        const saldoAtual = parseFloat(document.getElementById('quitar-saldo-valor').value);
        const valor = parseFloat(quitarValor.value) || 0;
        const tipoQuita = document.querySelector('input[name="tipo_quita"]:checked')?.value;
        
        if (!tipoQuita || valor <= 0) {
            quitarSaldoFinal.textContent = '';
            return;
        }
        
        let saldoFinal = saldoAtual;
        if (tipoQuita === 'debito') {
            saldoFinal = Math.min(0, saldoAtual + valor);
        } else {
            saldoFinal = Math.max(0, saldoAtual - valor);
        }
        
        quitarSaldoFinal.textContent = `R$ ${saldoFinal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        quitarSaldoFinal.className = saldoFinal < 0 ? 'negativo' : (saldoFinal > 0 ? '' : 'zero');
    }

    // Eventos para calcular saldo final
    if (quitarValor) {
        quitarValor.addEventListener('input', calcularSaldoFinal);
    }
    if (quitarDebito) {
        quitarDebito.addEventListener('change', calcularSaldoFinal);
    }
    if (quitarCredito) {
        quitarCredito.addEventListener('change', calcularSaldoFinal);
    }

    // Submissão do formulário de quitação
    if (formQuitar) {
        formQuitar.onsubmit = function(e) {
            e.preventDefault();
            msgModalQuitar.textContent = '';
            
            const jogadorId = document.getElementById('quitar-jogador-id').value;
            const tipoQuita = document.querySelector('input[name="tipo_quita"]:checked')?.value;
            const valor = parseFloat(quitarValor.value);
            const observacao = document.getElementById('quitar-observacao').value.trim();
            
            if (!tipoQuita) {
                msgModalQuitar.style.color = 'red';
                msgModalQuitar.textContent = 'Selecione o tipo de quitação.';
                return;
            }
            
            if (!valor || valor <= 0) {
                msgModalQuitar.style.color = 'red';
                msgModalQuitar.textContent = 'Informe um valor válido.';
                return;
            }
            
            const dados = {
                jogador_id: parseInt(jogadorId),
                tipo_quita: tipoQuita,
                valor: valor,
                observacao: observacao
            };
            
            fetch('../src/api/jogadores/quitar_saldo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    msgModalQuitar.style.color = 'green';
                    msgModalQuitar.textContent = data.message;
                    setTimeout(() => {
                        modalQuitar.style.display = 'none';
                        buscarJogadores(); // Atualiza a lista
                    }, 1500);
                } else {
                    msgModalQuitar.style.color = 'red';
                    msgModalQuitar.textContent = data.message || 'Erro ao realizar quitação.';
                }
            })
            .catch(() => {
                msgModalQuitar.style.color = 'red';
                msgModalQuitar.textContent = 'Erro ao realizar quitação.';
            });
        };
    }

    // Atualizar função de atribuir eventos para incluir quitar
    const originalAtribuirEventosAosBotoes = atribuirEventosAosBotoes;
    atribuirEventosAosBotoes = function() {
        originalAtribuirEventosAosBotoes();
        atribuirEventosQuitar();
    };

    // Inicializa eventos de quitar
    atribuirEventosQuitar();
}); 