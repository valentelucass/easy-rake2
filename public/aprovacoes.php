<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
require_once '../src/api/db_connect.php';
$conn = getConnection();
$user_id = $_SESSION['user_id'];
$unidade_info = null;
try {
    $stmt = $conn->prepare("SELECT u.id, u.nome, u.telefone, u.codigo_acesso, u.status, u.data_criacao FROM unidades u INNER JOIN associacoes_usuario_unidade aau ON u.id = aau.id_unidade WHERE aau.id_usuario = ? AND aau.status_aprovacao = 'Aprovado' LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $unidade_info = $result->fetch_assoc();
    }
    $stmt->close();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovações de Acesso</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/_aprovacoes.css">
</head>
<body>
<div class="app-container">
    <?php include '../src/includes/header-dashboard.php'; ?>
    <main id="main-content" class="dashboard-main">
        <div class="content-container">
            <!-- Bloco de informações do gestor e unidade -->
            <div class="card-box section" id="info-gestor-unidade" style="margin-bottom: 2rem; display: none;">
                <h2>Informações da Unidade</h2>
                <div class="info-unidade-box">
                    <div><b>Gestor:</b> <span id="gestor-nome"></span></div>
                    <div><b>Email do Gestor:</b> <span id="gestor-email"></span></div>
                    <div><b>Unidade:</b> <span id="unidade-nome"></span></div>
                    <div><b>Status:</b> <span id="unidade-status"></span></div>
                    <div><b>Telefone:</b> <span id="unidade-telefone"></span></div>
                    <div><b>Data de Criação:</b> <span id="unidade-data-criacao"></span></div>
                    <div><b>Código de Acesso:</b> <span id="codigo-acesso" class="codigo-acesso-badge"></span></div>
                    <button onclick="copiarCodigoAcesso()" class="btn-copiar-codigo">Copiar</button>
                </div>
            </div>
            <?php if ($unidade_info): ?>
            <div class="card-box section">
                <h2>Informações da Unidade</h2>
                <div class="unidade-info">
                    <div class="info-item"><strong>Nome da Unidade:</strong> <?= htmlspecialchars($unidade_info['nome']) ?></div>
                    <div class="info-item"><strong>Telefone:</strong> <?= htmlspecialchars($unidade_info['telefone']) ?></div>
                    <div class="info-item codigo-acesso"><strong>Código de Acesso da Unidade:</strong> <span class="codigo-texto"><?= htmlspecialchars($unidade_info['codigo_acesso']) ?></span> <button class="button button--small" onclick="copiarCodigo()">Copiar</button></div>
                    <div class="info-item"><strong>Status:</strong> <span class="status-badge <?= strtolower($unidade_info['status']) === 'ativa' ? 'status-ativo' : 'status-inativo' ?>"><?= htmlspecialchars($unidade_info['status']) ?></span></div>
                    <div class="info-item"><strong>Data de Criação:</strong> <?= date('d/m/Y H:i', strtotime($unidade_info['data_criacao'])) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-box section">
                <h2>Aprovações Pendentes</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Solicitante</th>
                                <th>CPF</th>
                                <th>Data Solicitação</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-aprovacoes-pendentes">
                            <tr><td colspan="7">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-box section">
                <h2>Histórico de Aprovações</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Solicitante</th>
                                <th>CPF</th>
                                <th>Data Solicitação</th>
                                <th>Status</th>
                                <th>Data Decisão</th>
                                <th>Gestor</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-aprovacoes-historico">
                            <tr><td colspan="8">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php include '../src/includes/footer.php'; ?>
</div>
<script>
function copiarCodigo() {
    const codigoElement = document.querySelector('.codigo-texto');
    const button = event.target;
    if (codigoElement) {
        const codigo = codigoElement.textContent;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(codigo).then(function() {
                feedbackCopiado(button);
            }).catch(function() {
                fallbackCopy(codigo, button);
            });
        } else {
            fallbackCopy(codigo, button);
        }
    }
}
function feedbackCopiado(button) {
    const originalText = button.textContent;
    button.textContent = 'Copiado!';
    button.style.background = '#28a745';
    setTimeout(() => {
        button.textContent = originalText;
        button.style.background = '';
    }, 1800);
}
function fallbackCopy(text, button) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand('copy');
        feedbackCopiado(button);
    } catch (err) {
        alert('Erro ao copiar código. Tente selecionar e copiar manualmente.');
    }
    document.body.removeChild(tempInput);
}
</script>
<script src="js/features/aprovacoes_acesso.js"></script>
</body>
</html> 