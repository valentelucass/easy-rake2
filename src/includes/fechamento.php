<!-- DASHBOARD SESSÃO ATIVA (BLOCOS EXTRAS) -->
<div class="card-box section" id="dashboard-sessao-ativa-section">
  <h3>Dashboard Sessão Ativa</h3>
<section id="dashboard-sessao-ativa" class="dashboard-sessao-ativa">
  <div class="dashboard-bloco" id="bloco-inventario-detalhado">
    <h4>Inventário Detalhado</h4>
    <div>Inventário Atual: <span id="inventario-atual"></span></div>
    <div>Inventário Real: <span id="inventario-real"></span></div>
    <div>Diferença: <span id="diferenca-inventario"></span></div>
  </div>
  <div class="dashboard-bloco" id="bloco-receitas-detalhado">
    <h4>Receitas Detalhadas</h4>
    <div>Rake Total: <span id="rake-total"></span></div>
    <div>Cashback Caixinhas: <span id="cashback-caixinhas"></span></div>
    <div>Receita Total: <span id="receita-total"></span></div>
  </div>
  <div class="dashboard-bloco" id="bloco-caixinhas">
    <h4>Caixinhas</h4>
    <div>Total Bruto: <span id="caixinhas-bruto"></span></div>
    <div>Total Líquido: <span id="caixinhas-liquido"></span></div>
    <div id="caixinhas-lista"></div>
  </div>
  <div class="dashboard-bloco" id="bloco-jogadores">
    <h4>Jogadores Ativos</h4>
    <div>Total: <span id="jogadores-total"></span></div>
    <button id="btn-expandir-jogadores" type="button">Expandir lista</button>
    <div id="jogadores-lista" style="display:none;"></div>
  </div>
  <div class="dashboard-bloco destaque" id="bloco-saldo-operacional">
    <h4>Saldo Operacional da Sessão</h4>
    <div id="saldo-operacional"></div>
  </div>
</section>
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