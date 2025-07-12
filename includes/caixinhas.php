<?php
// Controle de acesso por perfil
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    echo '<div class="alert alert-warning">Acesso restrito. Apenas Gestor e Caixa podem acessar esta aba.</div>';
    return;
}
?>
<div class="caixinhas-section">
    <button id="btn-criar-caixinha" class="btn btn-primary">+ Criar Nova Caixinha</button>
    <div id="lista-caixinhas">
        <!-- Caixinhas serão carregadas via JS -->
    </div>
</div>

<!-- Modal: Criar Nova Caixinha -->
<div id="modal-criar-caixinha" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-modal-criar">&times;</span>
        <h3>Criar Nova Caixinha</h3>
        <form id="form-criar-caixinha">
            <label>Nome da Caixinha</label>
            <input type="text" name="nome" required maxlength="40" placeholder="Ex: Equipe Domingo, Turno Noite, Bar, etc.">
            <label>Porcentagem de Cashback (%)</label>
            <input type="number" name="cashback" min="0" max="100" value="10" required>
            <label>Número de Participantes</label>
            <input type="number" name="participantes" min="1" required>
            <button type="submit" class="btn btn-success">Confirmar Criação</button>
        </form>
    </div>
</div>

<!-- Modal: Adicionar Valor -->
<div id="modal-adicionar-valor" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-modal-adicionar">&times;</span>
        <h3>Adicionar Valor à Caixinha</h3>
        <form id="form-adicionar-valor">
            <input type="hidden" name="id_caixinha">
            <label>Valor (R$)</label>
            <input type="number" name="valor" min="0.01" step="0.01" required>
            <button type="submit" class="btn btn-success">Adicionar Valor</button>
        </form>
    </div>
</div>

<!-- Modal: Detalhes da Caixinha -->
<div id="modal-detalhes-caixinha" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-modal-detalhes">&times;</span>
        <h3>Detalhes da Caixinha</h3>
        <div id="detalhes-caixinha-conteudo">
            <!-- Detalhes serão carregados via JS -->
        </div>
    </div>
</div>
<!-- Container para feedback visual -->
<div id="caixinhas-feedback"></div> 