<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Gestor') {
    echo json_encode(['success' => false, 'message' => 'Acesso restrito ao gestor.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Descobrir a unidade do gestor
$stmt = $conn->prepare("SELECT u.id FROM unidades u INNER JOIN associacoes_usuario_unidade aau ON u.id = aau.id_unidade WHERE aau.id_usuario = ? AND aau.perfil = 'Gestor' AND aau.status_aprovacao = 'Aprovado' LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Gestor não associado a nenhuma unidade.']);
    exit;
}
$unidade = $result->fetch_assoc();
$id_unidade = $unidade['id'];

$historico = [];

// Caixas aprovadas/rejeitadas
$sql = "SELECT a.id, a.tipo, a.referencia_id, a.solicitante_id, a.status, a.data_solicitacao, a.data_aprovacao, u.nome AS solicitante_nome, u.tipo_usuario, a.aprovador_id FROM aprovacoes a INNER JOIN usuarios u ON a.solicitante_id = u.id WHERE a.status IN ('Aprovado','Rejeitado') AND a.tipo = 'Caixa' AND a.referencia_id IN (SELECT c.id FROM caixas c WHERE c.operador_id IN (SELECT id_usuario FROM associacoes_usuario_unidade WHERE id_unidade = ?)) ORDER BY a.data_aprovacao DESC LIMIT 100";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_unidade);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['solicitante_nome'] = $row['solicitante_nome'] . ' (' . ucfirst($row['tipo_usuario']) . ')';
    $historico[] = $row;
}

// Sanger aprovados/rejeitados
$sql2 = "SELECT aau.id, 'Sanger' as tipo, aau.id_usuario as solicitante_id, u.nome as solicitante_nome, aau.data_criacao as data_solicitacao, aau.data_aprovacao, aau.status_aprovacao as status FROM associacoes_usuario_unidade aau INNER JOIN usuarios u ON aau.id_usuario = u.id WHERE aau.status_aprovacao IN ('Aprovado','Rejeitado','Removido') AND aau.id_unidade = ? AND aau.perfil != 'Gestor' ORDER BY aau.data_aprovacao DESC LIMIT 100";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('i', $id_unidade);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    $historico[] = $row;
}

usort($historico, function($a, $b) { return strtotime($b['data_aprovacao']) - strtotime($a['data_aprovacao']); });
echo json_encode(['success' => true, 'historico' => $historico]);
// $conn->close(); 