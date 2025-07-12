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

// Buscar aprovações pendentes de caixas e Sanger (funcionários)
$aprovacoes = [];

// Caixas pendentes (exemplo: status = 'Pendente' na tabela aprovacoes, tipo = 'Caixa')
$sql = "SELECT a.id, a.tipo, a.referencia_id, a.solicitante_id, a.status, a.data_solicitacao, u.nome AS solicitante_nome FROM aprovacoes a INNER JOIN usuarios u ON a.solicitante_id = u.id WHERE a.status = 'Pendente' AND a.tipo = 'Caixa' AND a.referencia_id IN (SELECT c.id FROM caixas c WHERE c.status = 'Aberto' AND c.operador_id IN (SELECT id_usuario FROM associacoes_usuario_unidade WHERE id_unidade = ?)) ORDER BY a.data_solicitacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_unidade);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $aprovacoes[] = $row;
}

// Sanger/Funcionários pendentes (status_aprovacao = 'Pendente' na associacoes_usuario_unidade)
$sql2 = "SELECT aau.id, 'Sanger' as tipo, aau.id_usuario as solicitante_id, u.nome as solicitante_nome, aau.data_criacao as data_solicitacao, aau.status_aprovacao as status FROM associacoes_usuario_unidade aau INNER JOIN usuarios u ON aau.id_usuario = u.id WHERE aau.status_aprovacao = 'Pendente' AND aau.id_unidade = ? AND aau.perfil != 'Gestor' ORDER BY aau.data_criacao DESC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('i', $id_unidade);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    $aprovacoes[] = $row;
}

// Resposta
usort($aprovacoes, function($a, $b) { return strtotime($b['data_solicitacao']) - strtotime($a['data_solicitacao']); });
echo json_encode(['success' => true, 'aprovacoes' => $aprovacoes]);
// $conn->close(); 