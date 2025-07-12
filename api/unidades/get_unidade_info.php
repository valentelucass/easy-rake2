<?php
session_start();
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

require_once '../db_connect.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Busca a unidade do usuário logado através da associação
    $query = "SELECT u.id, u.nome, u.telefone, u.codigo_acesso, u.status, u.data_criacao
              FROM unidades u
              INNER JOIN associacoes_usuario_unidade aau ON u.id = aau.id_unidade
              WHERE aau.id_usuario = ? AND aau.status_aprovacao = 'Aprovado'
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $unidade = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'unidade' => $unidade
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não está associado a nenhuma unidade ou não foi aprovado.'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar informações da unidade: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 