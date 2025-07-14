<div class="content-container">
    <div class="card-box section">
        <h2>Jogadores</h2>
        <!-- Formulário de Busca -->
        <div class="search-section">
            <form class="search-form">
                <input type="text" placeholder="Buscar jogador..." class="search-input">
                <button type="submit" class="button button--primary">Buscar</button>
            </form>
            <div class="novo-jogador-wrapper">
                <button id="btn-novo-jogador" class="button button--secondary">Novo Jogador</button>
            </div>
        </div>

        <!-- Lista de Jogadores -->
        <div class="table-container">
            <table class="data-table" id="tabela-jogadores">
                <thead>
                    <tr>
                        <th>NOME</th>
                        <th>CPF</th>
                        <th>TELEFONE</th>
                        <th>LIMITE DE CRÉDITO</th>
                        <th>SALDO ATUAL</th>
                        <th>DATA CADASTRO</th>
                        <th>SITUAÇÃO</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Conteúdo dinâmico via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Cadastro/Edição de Jogador -->
<div id="modal-jogador" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="fechar-modal-jogador">&times;</span>
        <h3 id="modal-jogador-titulo">Novo Jogador</h3>
        <form id="form-jogador">
            <label>Nome Completo*<br>
                <input type="text" name="nome" id="jogador-nome" required maxlength="100">
            </label><br>
            <label>CPF*<br>
                <input type="text" name="cpf" id="jogador-cpf" required maxlength="14" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">
            </label><br>
            <label>Telefone<br>
                <input type="text" name="telefone" id="jogador-telefone" maxlength="15">
            </label><br>
            <label>Limite de Crédito (R$)<br>
                <input type="number" name="limite_credito" id="jogador-limite" min="0" step="0.01" value="0.00">
            </label><br>
            <button type="submit" class="button button--primary">Cadastrar</button>
        </form>
        <div id="modal-jogador-msg" style="margin-top:10px;"></div>
    </div>
</div>

<!-- Modal de Quitação de Saldo -->
<div id="modal-quitar-saldo" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="fechar-modal-quitar">&times;</span>
        <h3 id="modal-quitar-titulo">Quitar Saldo</h3>
        <div class="quitar-info">
            <p><strong>Jogador:</strong> <span id="quitar-jogador-nome"></span></p>
            <p><strong>Saldo Atual:</strong> <span id="quitar-saldo-atual"></span></p>
        </div>
        <form id="form-quitar-saldo">
            <input type="hidden" id="quitar-jogador-id">
            <input type="hidden" id="quitar-saldo-valor">
            <div class="quitar-opcoes">
                <label class="quitar-opcao">
                    <input type="radio" name="tipo_quita" value="debito" id="quitar-debito">
                    <span class="quitar-opcao-texto">
                        <strong>Pagamento de Débito</strong><br>
                        <small>Jogador paga um valor para reduzir o saldo negativo</small>
                    </span>
                </label>
                <label class="quitar-opcao">
                    <input type="radio" name="tipo_quita" value="credito" id="quitar-credito">
                    <span class="quitar-opcao-texto">
                        <strong>Pagamento de Crédito</strong><br>
                        <small>Caixa paga um valor ao jogador referente a saldo positivo</small>
                    </span>
                </label>
            </div>
            <label>Valor (R$)*<br>
                <input type="number" name="valor" id="quitar-valor" min="0.01" step="0.01" required>
            </label><br>
            <label>Observação<br>
                <textarea name="observacao" id="quitar-observacao" rows="3" maxlength="255" placeholder="Observação sobre a quitação..."></textarea>
            </label><br>
            <div class="quitar-preview">
                <p><strong>Saldo após quitação:</strong> <span id="quitar-saldo-final"></span></p>
            </div>
            <button type="submit" class="button button--primary">Confirmar Quitação</button>
        </form>
        <div id="modal-quitar-msg" style="margin-top:10px;"></div>
    </div>
</div>

<!-- Scripts necessários para a aba funcionar -->
<link rel="stylesheet" href="css/pages/_jogadores.css">
<script src="js/features/jogadores.js"></script> 