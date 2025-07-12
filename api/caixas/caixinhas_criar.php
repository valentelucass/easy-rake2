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
$nome = trim($input['nome'] ?? '');
$cashback = intval($input['cashback'] ?? 0);
$participantes = intval($input['participantes'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$nome || $participantes < 1 || $cashback < 0 || $cashback > 100) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}
// Buscar caixa aberto do usuário
$stmt = $conn->prepare("SELECT id FROM caixas WHERE operador_id = ? AND status = 'Aberto' ORDER BY data_abertura DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$caixa = $res->fetch_assoc();
if (!$caixa) {
    echo json_encode(['success' => false, 'message' => 'Nenhum caixa aberto encontrado.']);
    exit;
}
$caixa_id = $caixa['id'];
$stmt = $conn->prepare("INSERT INTO caixinhas (caixa_id, nome, cashback_percent, participantes) VALUES (?, ?, ?, ?)");
$stmt->bind_param('isii', $caixa_id, $nome, $cashback, $participantes);
if ($stmt->execute()) {
    $id = $conn->insert_id;
    echo json_encode(['success' => true, 'caixinha' => [
        'id' => $id,
        'nome' => $nome,
        'cashback' => $cashback,
        'participantes' => $participantes
    ]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar caixinha.']);
}
$stmt->close(); 