<div class="dashboard-grid">
    <div class="card" id="inventario-card">
        <h3>Inventário</h3>
        <p>Abertura: <span id="inventario-abertura"></span></p>
        <p>Fichas Vendidas: <span id="inventario-fichas-vendidas"></span></p>
        <p>Fichas Devolvidas: <span id="inventario-fichas-devolvidas"></span></p>
        <p>Inventário Atual: <span id="inventario-atual-calculado"></span></p>
    </div>
    <div class="card" id="receitas-card">
        <h3>Receitas</h3>
        <p>Rake Total: <span id="receitas-rake"></span></p>
        <p>Cashback Caixinhas: <span id="receitas-cashback"></span></p>
        <p>Total: <span id="receitas-total"></span></p>
    </div>
    <div class="card" id="despesas-card">
        <h3>Despesas</h3>
        <p>Total: <span id="despesas-total"></span></p>
    </div>
    <div class="card" id="saldo-card">
        <h3>Saldo Operacional</h3>
        <p>Saldo Atual do Clube: <span id="saldo-operacional"></span></p>
    </div>
    <div class="card full-width" id="caixinhas-card">
        <h3>Caixinhas</h3>
        <p>Total Bruto: <span id="caixinhas-bruto"></span></p>
        <p>Total Líquido: <span id="caixinhas-liquido"></span></p>
        <div id="caixinhas-detalhes"></div>
    </div>
    <div class="card full-width" id="jogadores-card">
        <h3>Jogadores Ativos (<span id="jogadores-total"></span>)</h3>
        <table id="jogadores-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Fichas Compradas</th>
                    <th>Fichas Devolvidas</th>
                    <th>Saldo</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Resumo Financeiro -->
<div class="card-box section" id="resumo-financeiro-section">
    <h3>Resumo Financeiro</h3>
    <div class="resumo-grid">
        <div class="resumo-card">
            <div class="resumo-label">Inventário de Abertura (fichas)</div>
            <div class="resumo-valor" id="valor-inicial">R$ 0,00</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-label">Total Receitas</div>
            <div class="resumo-valor entrada" id="total-receitas">R$ 0,00</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-label">Total Despesas</div>
            <div class="resumo-valor saida" id="total-despesas">R$ 0,00</div>
        </div>
        <div class="resumo-card">
            <div class="resumo-label">Saldo Atual</div>
            <div class="resumo-valor saldo" id="saldo-atual">R$ 0,00</div>
        </div>
    </div>
</div>

<!-- Ações de Fechamento -->
<div class="card-box section" id="acoes-fechamento-section">
    <h3>Ações de Fechamento</h3>
    <div class="acoes-fechamento-grid">
        <div class="acao-card">
            <h4>Encerrar Caixa</h4>
            <p>Finaliza o caixa e registra o fechamento no sistema.</p>
            <button id="btn-encerrar-caixa" class="button button--primary">Encerrar Caixa</button>
        </div>
        <div class="acao-card">
            <h4>Exportar Relatórios</h4>
            <p>Gera relatórios em PDF e Excel com todos os dados do caixa.</p>
            <div class="botoes-exportacao">
                <button id="btn-exportar-pdf" class="button button--secondary">PDF</button>
                <button id="btn-exportar-excel" class="button button--secondary">Excel</button>
            </div>
        </div>
    </div>
</div>

<!-- Compilação de Dados -->
<div class="card-box section" id="compilacao-caixa-section">
    <div class="compilacao-header">
        <h3>Compilação de Dados de Caixa</h3>
        <div class="compilacao-actions">
            <button id="btn-atualizar-dados" class="button button--secondary">Atualizar</button>
        </div>
    </div>
    <div id="compilacao-caixa-loader" class="hidden">Carregando dados...</div>
    <div id="compilacao-caixa-erro" class="error-message hidden"></div>
    <div id="compilacao-caixa-tabela"></div>
</div>

<!-- Status do Caixa -->
<div class="card-box section" id="status-caixa-section">
    <h3>Status do Caixa</h3>
    <div class="status-info">
        <div class="status-item">
            <span class="status-label">Status:</span>
            <span class="status-badge status-aberto" id="status-caixa">ABERTO</span>
        </div>
        <div class="status-item">
            <span class="status-label">Operador:</span>
            <span id="operador-caixa">-</span>
        </div>
        <div class="status-item">
            <span class="status-label">Data de Abertura:</span>
            <span id="data-abertura">-</span>
        </div>
        <div class="status-item">
            <span class="status-label">Tempo Aberto:</span>
            <span id="tempo-aberto">-</span>
        </div>
    </div>
</div>