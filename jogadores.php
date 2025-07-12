<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco de dados
require_once 'api/db_connect.php';

// Busca informações do usuário logado
$user_id = $_SESSION['user_id'];
$nome_usuario = $_SESSION['nome_usuario'];
$perfil_usuario = $_SESSION['perfil'];

$jogadores_result = null;

// Busca jogadores recentes com tratamento de erro
try {
    $jogadores_query = "SELECT * FROM jogadores ORDER BY data_cadastro DESC LIMIT 10";
    $jogadores_result = $conn->query($jogadores_query);
} catch (Exception $e) {
    error_log("Erro ao buscar jogadores: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EASY RAKE - Jogadores</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/_jogadores.css">
</head>
<body>
<div class="app-container">
            <?php include 'includes/header-dashboard.php'; ?>
    <main id="main-content" class="dashboard-main">
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
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Telefone</th>
                                <th>Limite de Crédito</th>
                                <th>Saldo Atual</th>
                                <th>Data Cadastro</th>
                                <th>Situação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jogadores_result && $jogadores_result->num_rows > 0): ?>
                                <?php while ($jogador = $jogadores_result->fetch_assoc()): ?>
                                    <?php 
                                    $saldo = floatval($jogador['saldo_atual'] ?? 0);
                                    $limite = floatval($jogador['limite_credito'] ?? 0);
                                    $situacao = 'Em dia';
                                    if ($saldo < 0) {
                                        $situacao = 'Devedor';
                                        if ($limite > 0 && abs($saldo) > $limite) {
                                            $situacao = 'Limite Excedido';
                                        }
                                    } elseif ($saldo > 0) {
                                        $situacao = 'A receber';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($jogador['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($jogador['cpf']); ?></td>
                                        <td><?php echo htmlspecialchars($jogador['telefone']); ?></td>
                                        <td>R$ <?php echo number_format($limite, 2, ',', '.'); ?></td>
                                        <td class="saldo-cell" style="color:<?php echo ($saldo < 0 ? 'red' : ($saldo > 0 ? 'green' : 'inherit')); ?>">
                                            R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($jogador['data_cadastro'])); ?></td>
                                        <td><span class="status-badge status-<?php echo strtolower($situacao); ?>"><?php echo htmlspecialchars($situacao); ?></span></td>
                                        <td>
                                            <div class="acao-botoes">
                                                <button class="button button--small btn-quitar-saldo" data-id="<?php echo $jogador['id']; ?>" data-nome="<?php echo htmlspecialchars($jogador['nome']); ?>" data-saldo="<?php echo $saldo; ?>">Quitar</button>
                                                <button class="button button--small btn-editar-jogador" data-id="<?php echo $jogador['id']; ?>">Editar</button>
                                                <button class="button button--small button--danger btn-excluir-jogador" data-id="<?php echo $jogador['id']; ?>">Excluir</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">Nenhum jogador encontrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
            <?php include 'includes/footer.php'; ?>
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

    <script>
        function logout() {
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = 'api/auth/logout.php';
            }
        }
    </script>
    <script src="js/features/dashboard.js"></script>
    <script src="js/features/jogadores.js"></script>
</div>
</body>
</html> 