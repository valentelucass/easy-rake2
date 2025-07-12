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
$id_caixinha = intval($input['id_caixinha'] ?? 0);
$valor = floatval($input['valor'] ?? 0);
$user_id = $_SESSION['user_id'];
if ($id_caixinha < 1 || $valor <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO caixinhas_inclusoes (caixinha_id, valor, usuario_id) VALUES (?, ?, ?)");
$stmt->bind_param('idi', $id_caixinha, $valor, $user_id);
if ($stmt->execute()) {
    $id = $conn->insert_id;
    echo json_encode(['success' => true, 'inclusao' => [
        'id' => $id,
        'valor' => $valor,
        'usuario_id' => $user_id,
        'data_inclusao' => date('Y-m-d H:i:s')
    ]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar valor.']);
}
$stmt->close(); 