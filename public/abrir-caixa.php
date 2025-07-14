<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco de dados
require_once '../src/api/db_connect.php';
$conn = getConnection();

// Busca informações do usuário logado
$user_id = $_SESSION['user_id'];
$nome_usuario = $_SESSION['nome_usuario'];
$perfil_usuario = $_SESSION['perfil'];
$unidade_id = $_SESSION['unidade_id'];

// Inicializa variáveis para evitar erros
$stats = [
    'caixas_abertos' => 0,
    'jogadores_ativos' => 0,
    'aprovacoes_pendentes' => 0
];

$caixas_result = null;

// Busca estatísticas do dashboard com tratamento de erro
try {
    // Contar caixas abertos
    if ($perfil_usuario === 'Gestor') {
        // Gestor vê todos os caixas da unidade
        $stmt_cx = $conn->prepare("SELECT COUNT(*) as total FROM caixas WHERE status = 'Aberto' AND unidade_id = ?");
        $stmt_cx->bind_param('i', $unidade_id);
        $stmt_cx->execute();
        $res_cx = $stmt_cx->get_result();
        $stats['caixas_abertos'] = $res_cx->fetch_assoc()['total'] ?? 0;
        $stmt_cx->close();
    } else {
        // Caixa comum: só os próprios caixas
        $stmt_cx = $conn->prepare("SELECT COUNT(*) as total FROM caixas WHERE status = 'Aberto' AND funcionario_abertura_id = ?");
        $stmt_cx->bind_param('i', $_SESSION['funcionario_id']);
        $stmt_cx->execute();
        $res_cx = $stmt_cx->get_result();
        $stats['caixas_abertos'] = $res_cx->fetch_assoc()['total'] ?? 0;
        $stmt_cx->close();
    }

    // Contar jogadores ativos
    $stmt_jg = $conn->prepare("SELECT COUNT(*) as total FROM jogadores WHERE status = 'Ativo' AND unidade_id = ?");
    $stmt_jg->bind_param('i', $unidade_id);
    $stmt_jg->execute();
    $res_jg = $stmt_jg->get_result();
    $stats['jogadores_ativos'] = $res_jg->fetch_assoc()['total'] ?? 0;
    $stmt_jg->close();

    // Contar aprovações pendentes
    $stats['aprovacoes_pendentes'] = 0;
    if ($perfil_usuario === 'Gestor') {
        // Aprovações de acesso pendentes
        $stmt_aprov = $conn->prepare("
            SELECT COUNT(*) as total FROM aprovacoes_acesso aa 
            JOIN funcionarios f ON aa.funcionario_id = f.id 
            WHERE aa.status = 'Pendente' AND f.unidade_id = ?
        ");
        $stmt_aprov->bind_param('i', $unidade_id);
        $stmt_aprov->execute();
        $res_aprov = $stmt_aprov->get_result();
        $stats['aprovacoes_pendentes'] += $res_aprov->fetch_assoc()['total'] ?? 0;
        $stmt_aprov->close();
        
        // Aprovações operacionais pendentes
        $stmt_aprov_op = $conn->prepare("
            SELECT COUNT(*) as total FROM aprovacoes a 
            JOIN funcionarios f ON a.funcionario_id = f.id 
            WHERE a.status = 'Pendente' AND f.unidade_id = ?
        ");
        $stmt_aprov_op->bind_param('i', $unidade_id);
        $stmt_aprov_op->execute();
        $res_aprov_op = $stmt_aprov_op->get_result();
        $stats['aprovacoes_pendentes'] += $res_aprov_op->fetch_assoc()['total'] ?? 0;
        $stmt_aprov_op->close();
    }
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Busca últimos caixas abertos com tratamento de erro
try {
    if ($perfil_usuario === 'Gestor') {
        // Gestor vê todos os caixas da unidade
        $stmt = $conn->prepare("
            SELECT c.*, u.nome as operador, f.cargo 
            FROM caixas c 
            JOIN funcionarios f ON c.funcionario_abertura_id = f.id 
            JOIN usuarios u ON f.usuario_id = u.id 
            WHERE c.unidade_id = ? 
            ORDER BY c.data_abertura DESC 
            LIMIT 5
        ");
        $stmt->bind_param('i', $unidade_id);
        $stmt->execute();
        $caixas_result = $stmt->get_result();
    } else {
        // Caixa comum: só os próprios caixas
        $stmt = $conn->prepare("
            SELECT c.*, u.nome as operador, f.cargo 
            FROM caixas c 
            JOIN funcionarios f ON c.funcionario_abertura_id = f.id 
            JOIN usuarios u ON f.usuario_id = u.id 
            WHERE c.funcionario_abertura_id = ? 
            ORDER BY c.data_abertura DESC 
            LIMIT 5
        ");
        $stmt->bind_param('i', $_SESSION['funcionario_id']);
        $stmt->execute();
        $caixas_result = $stmt->get_result();
    }
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
            <?php include '../src/includes/header-dashboard.php'; ?>
    <main id="main-content" class="dashboard-main">
        <div class="content-container">
            <!-- Removido: bloco de estatísticas rápidas (cards) -->

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
                                            <td><?php echo htmlspecialchars($caixa['operador']) . ' (' . ucfirst($caixa['cargo']) . ')'; ?></td>
                                            <td>R$ <?php echo number_format($caixa['inventario_inicial'], 2, ',', '.'); ?></td>
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
            <?php include '../src/includes/footer.php'; ?>
</div>
<script>
    // Função de logout movida para auth.js
</script>
<script src="js/features/dashboard.js"></script>
<script src="js/features/abrir-caixa.js"></script>
<script src="js/features/notifications.js"></script>
</body>
</html> 