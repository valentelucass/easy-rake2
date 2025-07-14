<?php
// Verificar se o usuário tem permissão para acessar esta aba
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    return; // Ocultar aba para perfis não autorizados
}
?>

<!-- Card de Registro de Nova Despesa -->
<div class="card-box section gastos-card">
    <h3>Registrar Nova Despesa</h3>
    <form class="gastos-form-registro">
        <div class="gastos-form-row">
            <div class="gastos-form-group">
                <label for="descricao">Descrição da Despesa *</label>
                <input type="text" id="descricao" name="descricao" required placeholder="Ex: Compra de bebidas">
            </div>
            <div class="gastos-form-group">
                <label for="valor">Valor (R$) *</label>
                <input type="number" id="valor" name="valor" step="0.01" min="0.01" required placeholder="0,00">
            </div>
        </div>
        <div class="gastos-form-group">
            <label for="observacoes">Observações (Opcional)</label>
            <textarea id="observacoes" name="observacoes" placeholder="Observações adicionais sobre a despesa"></textarea>
        </div>
        <button type="submit" class="btn btn-primary gastos-btn-registrar">
            Registrar Despesa
        </button>
    </form>
</div>

<!-- Card de Histórico de Despesas -->
<div class="card-box section gastos-card">
    <div class="gastos-header-historico">
        <h3>Histórico de Despesas da Sessão</h3>
        <button class="btn btn-secondary gastos-btn-imprimir-todas">
            🖨 Imprimir Todas as Despesas
        </button>
    </div>
    <div class="gastos-tabela">
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Data/Hora</th>
                    <th>Operador</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="gastos-tbody">
                <!-- Dados serão carregados via JavaScript -->
            </tbody>
        </table>
    </div>
    <div class="gastos-totalizador">
        <strong>Total de Despesas da Sessão: R$ <span id="gastos-total">0,00</span></strong>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="gastos-modal-exclusao" class="gastos-modal" style="display: none;">
    <div class="gastos-modal-content">
        <button class="gastos-modal-close">&times;</button>
        <h3>Confirmar Exclusão</h3>
        <p>Tem certeza que deseja excluir esta despesa?</p>
        <div class="gastos-modal-acoes">
            <button class="btn btn-danger gastos-btn-confirmar-exclusao">Excluir</button>
            <button class="btn btn-secondary gastos-btn-cancelar-exclusao">Cancelar</button>
        </div>
    </div>
</div> 