// Gastos - Funcionalidades da aba de despesas
class GastosManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.carregarGastos();
    }

    bindEvents() {
        // Formulário de registro
        const form = document.querySelector('.gastos-form-registro');
        if (form) {
            form.addEventListener('submit', (e) => this.registrarGasto(e));
        }

        // Botão imprimir todas as despesas
        const btnImprimirTodas = document.querySelector('.gastos-btn-imprimir-todas');
        if (btnImprimirTodas) {
            btnImprimirTodas.addEventListener('click', () => this.imprimirTodasGastos());
        }

        // Modal de exclusão
        const modal = document.getElementById('gastos-modal-exclusao');
        if (modal) {
            const btnClose = modal.querySelector('.gastos-modal-close');
            const btnCancelar = modal.querySelector('.gastos-btn-cancelar-exclusao');
            const btnConfirmar = modal.querySelector('.gastos-btn-confirmar-exclusao');

            if (btnClose) btnClose.addEventListener('click', () => this.fecharModal());
            if (btnCancelar) btnCancelar.addEventListener('click', () => this.fecharModal());
            if (btnConfirmar) btnConfirmar.addEventListener('click', () => this.confirmarExclusao());

            // Fechar modal ao clicar fora
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.fecharModal();
            });
        }
    }

    async registrarGasto(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const dados = {
            descricao: formData.get('descricao').trim(),
            valor: parseFloat(formData.get('valor')),
            observacoes: formData.get('observacoes').trim()
        };

        // Validação
        if (!dados.descricao) {
            this.mostrarAlerta('Por favor, informe a descrição da despesa.', 'error');
            return;
        }

        if (!dados.valor || dados.valor <= 0) {
            this.mostrarAlerta('Por favor, informe um valor válido.', 'error');
            return;
        }

        try {
            const response = await fetch('../src/api/caixas/registrar_gasto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarAlerta('Despesa registrada com sucesso!', 'success');
                e.target.reset();
                this.carregarGastos();
                this.imprimirReciboGasto(result.data);
            } else {
                this.mostrarAlerta(result.message || 'Erro ao registrar despesa.', 'error');
            }
        } catch (error) {
            console.error('Erro ao registrar gasto:', error);
            this.mostrarAlerta('Erro de conexão. Tente novamente.', 'error');
        }
    }

    async carregarGastos() {
        try {
            const response = await fetch('../src/api/caixas/listar_gastos.php');
            const result = await response.json();

            if (result.success) {
                this.renderizarTabelaGastos(result.data);
                this.atualizarTotalizador(result.data);
            } else {
                console.error('Erro ao carregar gastos:', result.message);
            }
        } catch (error) {
            console.error('Erro ao carregar gastos:', error);
        }
    }

    renderizarTabelaGastos(gastos) {
        const tbody = document.getElementById('gastos-tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (gastos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--cor-texto-secundario);">
                        Nenhuma despesa registrada nesta sessão.
                    </td>
                </tr>
            `;
            return;
        }

        gastos.forEach(gasto => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(gasto.descricao)}</td>
                <td>R$ ${parseFloat(gasto.valor).toFixed(2).replace('.', ',')}</td>
                <td>${this.formatarDataHora(gasto.data_hora)}</td>
                <td>${this.escapeHtml(gasto.operador)} (${gasto.perfil})</td>
                <td>
                    <button class="btn gastos-btn gastos-btn--imprimir" data-id="${gasto.id}" title="Imprimir Recibo">
                        🖨
                    </button>
                    <button class="btn gastos-btn gastos-btn--excluir" data-id="${gasto.id}" title="Excluir">
                        🗑
                    </button>
                </td>
            `;

            // Event listeners para os botões
            const btnImprimir = row.querySelector('.gastos-btn--imprimir');
            const btnExcluir = row.querySelector('.gastos-btn--excluir');

            if (btnImprimir) {
                btnImprimir.addEventListener('click', () => this.imprimirReciboGasto(gasto));
            }

            if (btnExcluir) {
                btnExcluir.addEventListener('click', () => this.abrirModalExclusao(gasto));
            }

            tbody.appendChild(row);
        });
    }

    atualizarTotalizador(gastos) {
        const totalElement = document.getElementById('gastos-total');
        if (!totalElement) return;

        const total = gastos.reduce((sum, gasto) => sum + parseFloat(gasto.valor), 0);
        totalElement.textContent = total.toFixed(2).replace('.', ',');
    }

    abrirModalExclusao(gasto) {
        this.gastoParaExcluir = gasto;
        const modal = document.getElementById('gastos-modal-exclusao');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    fecharModal() {
        const modal = document.getElementById('gastos-modal-exclusao');
        if (modal) {
            modal.style.display = 'none';
        }
        this.gastoParaExcluir = null;
    }

    async confirmarExclusao() {
        if (!this.gastoParaExcluir) return;

        try {
            const response = await fetch('../src/api/caixas/excluir_gasto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: this.gastoParaExcluir.id })
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarAlerta('Despesa excluída com sucesso!', 'success');
                this.fecharModal();
                this.carregarGastos();
            } else {
                this.mostrarAlerta(result.message || 'Erro ao excluir despesa.', 'error');
            }
        } catch (error) {
            console.error('Erro ao excluir gasto:', error);
            this.mostrarAlerta('Erro de conexão. Tente novamente.', 'error');
        }
    }

    async imprimirReciboGasto(gasto) {
        try {
            const response = await fetch('../src/api/caixas/imprimir_recibo_gasto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: gasto.id })
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarAlerta('Recibo enviado para impressão!', 'success');
            } else {
                this.mostrarAlerta(result.message || 'Erro ao imprimir recibo.', 'error');
            }
        } catch (error) {
            console.error('Erro ao imprimir recibo:', error);
            this.mostrarAlerta('Erro de conexão. Tente novamente.', 'error');
        }
    }

    async imprimirTodasGastos() {
        try {
            const response = await fetch('../src/api/caixas/imprimir_todas_gastos.php', {
                method: 'POST'
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarAlerta('Relatório de todas as despesas enviado para impressão!', 'success');
            } else {
                this.mostrarAlerta(result.message || 'Erro ao imprimir relatório.', 'error');
            }
        } catch (error) {
            console.error('Erro ao imprimir todas as gastos:', error);
            this.mostrarAlerta('Erro de conexão. Tente novamente.', 'error');
        }
    }

    formatarDataHora(dataHora) {
        const data = new Date(dataHora);
        return data.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    mostrarAlerta(mensagem, tipo = 'info') {
        // Usar o sistema de notificações existente se disponível
        if (window.showNotification) {
            window.showNotification(mensagem, tipo);
        } else {
            alert(mensagem);
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    new GastosManager();
}); 