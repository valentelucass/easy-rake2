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
$id_caixinha = intval($_GET['id_caixinha'] ?? 0);
if ($id_caixinha < 1) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}
$stmt = $conn->prepare("SELECT i.id, i.valor, i.data_inclusao, u.nome, u.perfil FROM caixinhas_inclusoes i JOIN usuarios u ON i.usuario_id = u.id WHERE i.caixinha_id = ? ORDER BY i.data_inclusao ASC");
$stmt->bind_param('i', $id_caixinha);
$stmt->execute();
$res = $stmt->get_result();
$inclusoes = [];
while ($row = $res->fetch_assoc()) {
    $inclusoes[] = $row;
}
echo json_encode(['success' => true, 'inclusoes' => $inclusoes]);
$stmt->close(); 