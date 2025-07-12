<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}
$input = json_decode(file_get_contents('php://input'), true);
$caixa_id = $input['id'] ?? null;
if (!$caixa_id) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa não informado.']);
    exit;
}
try {
    $stmt = $conn->prepare("UPDATE caixas SET status = 'Fechado', data_fechamento = NOW() WHERE id = ? AND status = 'Aberto'");
    $stmt->bind_param('i', $caixa_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Caixa encerrado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível encerrar o caixa.']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao encerrar caixa.']);
}
$conn->close(); 