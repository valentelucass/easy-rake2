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

// Inicializa variáveis para evitar erros
$stats = [
    'aprovacoes_pendentes' => 0
];

// Busca informações da unidade do usuário logado
$unidade_info = null;
try {
    $unidade_query = "SELECT u.id, u.nome, u.telefone, u.codigo_acesso, u.status, u.data_criacao
                      FROM unidades u
                      INNER JOIN associacoes_usuario_unidade aau ON u.id = aau.id_unidade
                      WHERE aau.id_usuario = ? AND aau.status_aprovacao = 'Aprovado'
                      LIMIT 1";
    
    $stmt = $conn->prepare($unidade_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $unidade_info = $result->fetch_assoc();
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Erro ao buscar informações da unidade: " . $e->getMessage());
}

// Busca estatísticas de aprovações
try {
    $stats_query = "SELECT COUNT(*) as aprovacoes_pendentes FROM aprovacoes WHERE status = 'Pendente'";
    $stats_result = $conn->query($stats_query);
    if ($stats_result) {
        $stats = $stats_result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EASY RAKE - Aprovações</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/_aprovacoes.css">
</head>
<body>
<div class="app-container">
            <?php include 'includes/header-dashboard.php'; ?>
    <!-- conteúdo principal -->
    <main id="main-content" class="dashboard-main">
        <div class="content-container">
            
            <!-- Seção do Código de Acesso da Unidade -->
            <?php if ($unidade_info): ?>
            <div class="card-box section">
                <h2>Informações da Unidade</h2>
                <div class="unidade-info">
                    <div class="info-item">
                        <strong>Nome da Unidade:</strong> <?php echo htmlspecialchars($unidade_info['nome']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Telefone:</strong> <?php echo htmlspecialchars($unidade_info['telefone']); ?>
                    </div>
                    <div class="info-item codigo-acesso">
                        <strong>Código de Acesso da Unidade:</strong> 
                        <span class="codigo-texto"><?php echo htmlspecialchars($unidade_info['codigo_acesso']); ?></span>
                        <button class="button button--small" onclick="copiarCodigo()">Copiar</button>
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong>
                        <?php
                            $status = htmlspecialchars($unidade_info['status']);
                            $statusClass = strtolower($status) === 'ativa' ? 'status-badge status-ativo' : 'status-badge status-inativo';
                        ?>
                        <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($unidade_info['data_criacao'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-box section">
                <h2>Aprovações</h2>
                <p>Gerencie as aprovações pendentes do sistema.</p>
                <div class="approvals-grid">
                    <div class="approval-card">
                        <h3>Aprovações Pendentes</h3>
                        <p class="approval-count"><?php echo $stats['aprovacoes_pendentes'] ?? 0; ?> pendentes</p>
                        <button class="button button--primary">Ver Todas</button>
                    </div>
                    <div class="approval-card">
                        <h3>Aprovações Aprovadas</h3>
                        <p class="approval-count">0 aprovadas hoje</p>
                        <button class="button button--secondary">Ver Histórico</button>
                    </div>
                    <div class="approval-card">
                        <h3>Aprovações Rejeitadas</h3>
                        <p class="approval-count">0 rejeitadas hoje</p>
                        <button class="button button--secondary">Ver Histórico</button>
                    </div>
                    <div class="approval-card">
                        <h3>Total de Aprovações</h3>
                        <p class="approval-count">0 no total</p>
                        <button class="button button--secondary">Ver Relatório</button>
                    </div>
                </div>
            </div>

            <div class="card-box section">
                <h2>Últimas Aprovações</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Solicitante</th>
                                <th>Data Solicitação</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6">Nenhuma aprovação encontrada</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
            <?php include 'includes/footer.php'; ?>
</div>
    <script>
        function logout() {
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = 'api/auth/logout.php';
            }
        }

        function copiarCodigo() {
            const codigoElement = document.querySelector('.codigo-texto');
            const button = event.target;
            if (codigoElement) {
                const codigo = codigoElement.textContent;
                // Tenta usar a API moderna
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
            // Cria um input temporário para copiar
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
    <script src="js/features/aprovacoes.js"></script>
    <script src="js/features/dashboard.js"></script>
    <script src="js/features/notifications.js"></script>
</body>
</html> 