<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}

try {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT c.id, u.nome as operador, u.tipo_usuario, c.valor_inicial, c.data_abertura, c.status FROM caixas c LEFT JOIN usuarios u ON c.operador_id = u.id WHERE c.status = 'Aberto' AND c.operador_id = $user_id ORDER BY c.data_abertura DESC";
    $result = $conn->query($sql);
    $caixas = [];
    while ($row = $result->fetch_assoc()) {
        $row['operador'] = $row['operador'] . ' (' . ucfirst($row['tipo_usuario']) . ')';
        $caixas[] = $row;
    }
    echo json_encode(['success' => true, 'caixas' => $caixas]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar caixas abertos.']);
}
//$conn->close(); 