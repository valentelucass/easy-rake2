<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['Gestor', 'Caixa'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$caixa_id = intval($input['caixa_id'] ?? 0);
$valor = floatval($input['valor'] ?? 0);
$usuario_nome = $_SESSION['nome'] ?? 'Desconhecido';

if ($caixa_id <= 0 || $valor <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$sql = "INSERT INTO rake (caixa_id, valor, data_hora, usuario_nome) VALUES (?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ids', $caixa_id, $valor, $usuario_nome);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]); 