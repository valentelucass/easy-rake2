<?php
// Inicia a sessão
session_start();
header('Content-Type: application/json');

// Verifica autenticação
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

require_once '../db_connect.php';

$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');

if ($mes < 1 || $mes > 12) {
    echo json_encode(['success' => false, 'message' => 'Mês inválido.']);
    exit;
}

$primeiroDia = sprintf('%04d-%02d-01', $ano, $mes);
$ultimoDia = date('Y-m-t', strtotime($primeiroDia));

$user_id = $_SESSION['user_id'];

$sql = "SELECT id, tipo, status, data_geracao, HOUR(data_geracao) as hora, MINUTE(data_geracao) as minuto, arquivo, mensagem_erro
        FROM relatorios_historico
        WHERE id_usuario = ? AND data_geracao BETWEEN ? AND ?
        ORDER BY data_geracao ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $user_id, $primeiroDia, $ultimoDia);
$stmt->execute();
$result = $stmt->get_result();

$relatorios = [];
while ($row = $result->fetch_assoc()) {
    $dia = date('Y-m-d', strtotime($row['data_geracao']));
    $relatorios[$dia][] = [
        'id' => $row['id'],
        'tipo' => $row['tipo'],
        'status' => $row['status'],
        'hora' => sprintf('%02d:%02d', $row['hora'], $row['minuto']),
        'arquivo' => $row['arquivo'],
        'mensagem_erro' => $row['mensagem_erro']
    ];
}

$stmt->close();
// $conn->close(); 