<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['Gestor', 'Caixa'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$caixa_id = isset($_GET['caixa_id']) ? intval($_GET['caixa_id']) : 0;
if ($caixa_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa inválido.']);
    exit;
}

$sql = "SELECT valor, data_hora, usuario_nome FROM rake WHERE caixa_id = ? ORDER BY data_hora DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $caixa_id);
$stmt->execute();
$res = $stmt->get_result();
$registros = [];
while ($row = $res->fetch_assoc()) {
    $row['data_hora_formatada'] = date('d/m/Y H:i', strtotime($row['data_hora']));
    $registros[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'registros' => $registros]); 