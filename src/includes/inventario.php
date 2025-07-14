<?php
// Controle de acesso por perfil
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    echo '<div class="alert alert-warning">Acesso restrito. Apenas Gestor e Caixa podem acessar esta aba.</div>';
    return;
}
?>
<div class="content-container">
  <div class="card-box section">
    <h2>Inventário do Caixa</h2>
    <div class="inventario-dados-grid">
      <div class="inventario-dado-card">
        <span class="inventario-label">Valor de Abertura</span>
        <span class="inventario-valor" id="inventario-valor-abertura">R$ 0,00</span>
      </div>
      <div class="inventario-dado-card">
        <span class="inventario-label">Valor Atual Calculado</span>
        <span class="inventario-valor" id="inventario-valor-atual">R$ 0,00</span>
      </div>
      <div class="inventario-dado-card">
        <span class="inventario-label">Fichas em Jogo</span>
        <span class="inventario-valor" id="inventario-fichas-jogo">R$ 0,00</span>
      </div>
    </div>
  </div>

  <div class="card-box section">
    <h3>Conferência Manual</h3>
    <div class="inventario-conferencia-box">
      <form id="form-inventario-conferencia">
        <label for="inventario-real" class="inventario-label">Inventário Real (Contagem Física)</label>
        <input type="number" step="0.01" min="0" id="inventario-real" name="inventario_real" required placeholder="Digite o valor contado em R$">
        <button type="submit" class="button button--primary">Conferir</button>
      </form>
      <div id="inventario-resultado"></div>
    </div>
  </div>

  <div class="card-box section">
    <h3>Histórico de Conferências</h3>
    <div class="inventario-historico-box">
      <div class="inventario-tabela">
        <table>
          <thead>
            <tr>
              <th>Data/Hora</th>
              <th>Valor Informado</th>
              <th>Diferença</th>
              <th>Operador</th>
              <th>Resultado</th>
            </tr>
          </thead>
          <tbody id="inventario-historico-tbody">
            <!-- Linhas serão preenchidas via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div> 