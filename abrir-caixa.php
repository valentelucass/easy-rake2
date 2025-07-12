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
    'caixas_abertos' => 0,
    'jogadores_ativos' => 0,
    'aprovacoes_pendentes' => 0
];

$caixas_result = null;

// Busca estatísticas do dashboard com tratamento de erro
try {
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM caixas WHERE status = 'Aberto') as caixas_abertos,
        (SELECT COUNT(*) FROM jogadores WHERE status = 'Ativo') as jogadores_ativos,
        (SELECT COUNT(*) FROM aprovacoes WHERE status = 'Pendente') as aprovacoes_pendentes";

    $stats_result = $conn->query($stats_query);
    if ($stats_result) {
        $stats = $stats_result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Busca últimos caixas abertos com tratamento de erro
try {
    $caixas_query = "SELECT c.*, u.nome as operador, u.tipo_usuario FROM caixas c LEFT JOIN usuarios u ON c.operador_id = u.id WHERE c.operador_id = $user_id ORDER BY c.data_abertura DESC LIMIT 5";
    $caixas_result = $conn->query($caixas_query);
} catch (Exception $e) {
    error_log("Erro ao buscar caixas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EASY RAKE - Abrir Novo Caixa</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<div class="app-container">
            <?php include 'includes/header-dashboard.php'; ?>
    <main id="main-content" class="dashboard-main">
        <div class="content-container">
            <div class="card-box section">
                <h2>Abrir Novo Caixa</h2>
                <!-- Estatísticas Rápidas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>CAIXAS ABERTOS</h3>
                        <div class="stat-number"><?php echo $stats['caixas_abertos'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Jogadores Ativos</h3>
                        <p class="stat-number"><?php echo $stats['jogadores_ativos'] ?? 0; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Aprovações Pendentes</h3>
                        <p class="stat-number"><?php echo $stats['aprovacoes_pendentes'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="card-box section">
                <h2>Abertura de Caixa</h2>
                <form id="inventario-fichas-form" class="form" autocomplete="off">
                    <h4>Inventário Inicial de Fichas</h4>
                    <p class="form-instructions">Informe as denominações de fichas e quantidades para abertura do caixa.</p>
                    <div id="fichas-lista">
                        <!-- Linhas de ficha serão inseridas aqui pelo JS -->
                    </div>
                    <div class="botoes-acao-caixa">
                        <button type="button" id="btn-adicionar-ficha" class="btn btn-adicionar-ficha">Adicionar Ficha</button>
                        <button type="submit" class="btn btn-abrir-caixa">Abrir Caixa</button>
                    </div>
                    <div id="total-abertura" class="total-abertura-card">Total de Abertura: R$ 0,00</div>
                    <div id="caixa-aberto-sucesso" style="display:none; margin-top:1.5rem;"></div>
                </form>
            </div>

            <div class="card-box section">
                <h2>Caixas Abertos</h2>
                <div class="table-container">
                    <table class="data-table" id="tabela-caixas-abertos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Operador</th>
                                <th>Valor Inicial</th>
                                <th>Data Abertura</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Linhas serão preenchidas via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-box section">
                <h2>Últimos Caixas</h2>
                <!-- Últimos Caixas -->
                <div class="recent-section">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Operador</th>
                                    <th>Valor Inicial</th>
                                    <th>Data Abertura</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($caixas_result && $caixas_result->num_rows > 0): ?>
                                    <?php while ($caixa = $caixas_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($caixa['id']); ?></td>
                                            <td><?php echo htmlspecialchars($caixa['operador']) . ' (' . ucfirst($caixa['tipo_usuario']) . ')'; ?></td>
                                            <td>R$ <?php echo number_format($caixa['valor_inicial'], 2, ',', '.'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($caixa['data_abertura'])); ?></td>
                                            <td><span class="status-badge status-<?php echo strtolower($caixa['status']); ?>"><?php echo htmlspecialchars($caixa['status']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Nenhum caixa encontrado</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
</script>
<script src="js/features/dashboard.js"></script>
<script src="js/features/abrir-caixa.js"></script>
<script src="js/features/notifications.js"></script>
</body>
</html> 