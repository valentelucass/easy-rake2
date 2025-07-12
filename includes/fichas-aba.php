<div class="content-container">
  <!-- Box 1: Busca de jogador -->
  <div class="card-box section fichas-card">
    <h2>Buscar Jogador</h2>
    <form class="fichas-busca-form">
      <input type="text" id="fichas-busca-input" placeholder="Buscar por nome, CPF ou telefone...">
      <button type="button" id="fichas-busca-btn" class="button button--primary">Buscar</button>
    </form>
    <div id="fichas-busca-resultados"></div>
  </div>

  <!-- Box 2: Dados do jogador selecionado -->
  <div class="card-box section fichas-card" id="fichas-dados-jogador-box" style="display:none;">
    <h2>Dados do Jogador</h2>
    <div class="fichas-dados-jogador" id="fichas-dados-jogador">
      <!-- Preenchido via JS: nome, cpf, telefone, saldo, limite, situação -->
    </div>
  </div>

  <!-- Box 3: Transação de fichas -->
  <div class="card-box section fichas-card" id="fichas-transacao-box" style="display:none;">
    <h2>Transação de Fichas</h2>
    <div id="fichas-alerta-credito" class="fichas-alerta-credito" style="display:none;"></div>
    <form id="fichas-form-transacao" class="fichas-form-transacao">
      <input type="number" id="fichas-valor" placeholder="Valor das fichas" min="1" step="1" required>
      <button type="submit" id="fichas-btn-vender" class="button button--primary">Vender Fichas</button>
      <button type="submit" id="fichas-btn-devolver" class="button button--secondary">Devolver Fichas</button>
    </form>
  </div>

  <!-- Box 4: Tabela de jogadores ativos -->
  <div class="card-box section fichas-card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
      <h2 style="margin-bottom: 0;">Jogadores Ativos na Sessão</h2>
      <button id="fichas-btn-imprimir-todas" class="button button--primary">Imprimir Todas as Transações</button>
    </div>
    <div class="fichas-tabela">
      <table id="fichas-tabela-jogadores" class="historico-table">
        <thead>
          <tr>
            <th>Nome do Jogador</th>
            <th>Fichas Compradas</th>
            <th>Fichas Devolvidas</th>
            <th>Saldo Atual</th>
            <th>Re-buy</th>
            <th>Devolução Rápida</th>
            <th>Detalhes</th>
            <th><span class="icon-impressora" title="Imprimir Recibo"></span></th>
          </tr>
        </thead>
        <tbody>
          <!-- Preenchido via JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de detalhes do jogador -->
  <div id="fichas-modal-detalhes" class="fichas-modal" style="display:none;">
    <div class="fichas-modal-content">
      <span class="fichas-modal-close" id="fichas-modal-detalhes-close">&times;</span>
      <h3>Detalhes do Jogador</h3>
      <div id="fichas-modal-detalhes-conteudo">
        <!-- Conteúdo dinâmico, inclua a tabela com class=historico-table -->
      </div>
      <button id="fichas-modal-detalhes-imprimir" class="button button--primary">Imprimir Detalhes</button>
    </div>
  </div>
</div>
<link rel="stylesheet" href="css/pages/_fichas.css">
<script src="js/features/caixa-dashboard/fichas.js"></script> 