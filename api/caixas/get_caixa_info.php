<?php
session_start();
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

require_once '../db_connect.php';

$caixa_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$caixa_id) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa não informado.']);
    exit;
}

try {
    // Busca informações do caixa com dados do operador
    $query = "SELECT c.id, c.valor_inicial, c.valor_final, c.status, c.data_abertura, c.data_fechamento, c.observacoes,
                     u.nome as operador_nome, u.id as operador_id
              FROM caixas c
              LEFT JOIN usuarios u ON c.operador_id = u.id
              WHERE c.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $caixa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $caixa = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'caixa' => $caixa
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Caixa não encontrado.'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar informações do caixa: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 