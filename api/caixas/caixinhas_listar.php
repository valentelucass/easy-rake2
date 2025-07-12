<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}
$user_id = $_SESSION['user_id'];
// Buscar caixa aberto do usuário
$stmt = $conn->prepare("SELECT id FROM caixas WHERE operador_id = ? AND status = 'Aberto' ORDER BY data_abertura DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$caixa = $res->fetch_assoc();
if (!$caixa) {
    echo json_encode(['success' => true, 'caixinhas' => []]);
    exit;
}
$caixa_id = $caixa['id'];
$stmt = $conn->prepare("SELECT id, nome, cashback_percent as cashback, participantes FROM caixinhas WHERE caixa_id = ? ORDER BY data_criacao ASC");
$stmt->bind_param('i', $caixa_id);
$stmt->execute();
$res = $stmt->get_result();
$caixinhas = [];
while ($row = $res->fetch_assoc()) {
    $caixinhas[] = $row;
}
echo json_encode(['success' => true, 'caixinhas' => $caixinhas]);
$stmt->close(); 