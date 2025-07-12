<?php
// Inicia a sessão
session_start();

// Define o cabeçalho de resposta para JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// Inclui a conexão com o banco de dados
require_once '../db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}

try {
    // Busca estatísticas do dashboard
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM caixas WHERE status = 'Aberto') as caixas_abertos,
        (SELECT COUNT(*) FROM jogadores WHERE status = 'Ativo') as jogadores_ativos,
        (SELECT COUNT(*) FROM aprovacoes WHERE status = 'Pendente') as aprovacoes_pendentes,
        (SELECT COUNT(*) FROM caixas WHERE DATE(data_abertura) = CURDATE()) as caixas_hoje,
        (SELECT COUNT(*) FROM jogadores WHERE DATE(data_cadastro) = CURDATE()) as jogadores_hoje,
        (SELECT SUM(valor_inicial) FROM caixas WHERE status = 'Aberto') as valor_total_caixas";

    $stats_result = $conn->query($stats_query);
    $stats = $stats_result->fetch_assoc();
    
    // Busca últimas atividades
    $atividades_query = "SELECT 'caixa' as tipo, c.id, c.valor_inicial, c.data_abertura as data, u.nome as operador, u.tipo_usuario FROM caixas c LEFT JOIN usuarios u ON c.operador_id = u.id WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY c.data_abertura DESC LIMIT 5";
    
    $atividades_result = $conn->query($atividades_query);
    $atividades = [];
    
    while ($row = $atividades_result->fetch_assoc()) {
        $row['operador'] = $row['operador'] . ' (' . ucfirst($row['tipo_usuario']) . ')';
        $atividades[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'atividades' => $atividades,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar estatísticas.',
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 