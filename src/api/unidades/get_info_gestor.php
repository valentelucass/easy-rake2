<?php
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json');

error_log('[DEBUG][get_info_gestor] SESSION: ' . print_r($_SESSION, true));
if (!isset($_SESSION['user_id']) || !isset($_SESSION['unidade_id'])) {
    sendUnauthorized();
    exit;
}

$conn = getConnection();
if (!$conn) sendInternalError('Erro de conexão com banco');

try {
    // Forçar uso da função da auth.php (sessão)
    $unidade_id = isset($_SESSION['unidade_id']) ? $_SESSION['unidade_id'] : null;
    error_log('[DEBUG] unidade_id da sessão: ' . print_r($unidade_id, true));
    if (!$unidade_id) sendValidationError('Gestor não vinculado a nenhuma unidade');
    $stmt = $conn->prepare('SELECT u.id, u.nome, u.telefone, u.codigo_acesso, u.status, u.data_criacao, us.nome as gestor_nome, us.email as gestor_email FROM unidades u LEFT JOIN funcionarios f ON f.unidade_id = u.id AND f.cargo = "Gestor" LEFT JOIN usuarios us ON f.usuario_id = us.id WHERE u.id = ?');
    $stmt->bind_param('i', $unidade_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unidade = $result->fetch_assoc();
    $stmt->close();
    if (!$unidade) sendNotFound('Unidade não encontrada');
    // Formatar dados extras
    $unidade['status_texto'] = ($unidade['status'] === 'Ativa' || $unidade['status'] === 'Aberto') ? 'Ativa' : 'Inativa';
    $unidade['data_criacao_formatada'] = date('d/m/Y H:i', strtotime($unidade['data_criacao']));
    if ($unidade['telefone']) $unidade['telefone'] = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $unidade['telefone']);
    sendResponse(true, 'Unidade encontrada', 200, $unidade);
} catch (Exception $e) {
    sendInternalError('Erro ao buscar info da unidade: ' . $e->getMessage());
}
$conn->close(); 