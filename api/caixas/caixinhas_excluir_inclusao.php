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
$input = json_decode(file_get_contents('php://input'), true);
$id_inclusao = intval($input['id_inclusao'] ?? 0);
if ($id_inclusao < 1) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM caixinhas_inclusoes WHERE id = ?");
$stmt->bind_param('i', $id_inclusao);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir inclusão.']);
}
$stmt->close(); 