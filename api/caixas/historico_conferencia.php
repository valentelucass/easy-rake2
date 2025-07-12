<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

// Verifica autenticação e permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['perfil'] ?? '', ['Gestor', 'Caixa'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$caixa_id = isset($_GET['caixa_id']) ? intval($_GET['caixa_id']) : 0;
if ($caixa_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa inválido.']);
    exit;
}

// Exemplo de tabela: historico_conferencia (id, caixa_id, valor_informado, diferenca, operador_id, resultado, data_hora)
$sql = "SELECT h.data_hora, h.valor_informado, h.diferenca, u.nome as operador, h.resultado
        FROM historico_conferencia h
        LEFT JOIN usuarios u ON h.operador_id = u.id
        WHERE h.caixa_id = ?
        ORDER BY h.data_hora DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $caixa_id);
$stmt->execute();
$res = $stmt->get_result();
$historico = [];
while ($row = $res->fetch_assoc()) {
    $historico[] = [
        'data_hora' => $row['data_hora'],
        'valor_informado' => $row['valor_informado'],
        'diferenca' => $row['diferenca'],
        'operador' => $row['operador'],
        'resultado' => $row['resultado']
    ];
}
$stmt->close();

echo json_encode(['success' => true, 'historico' => $historico]); 