<?php
/**
 * API para Buscar Funcionário por ID - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageEmployees()) sendForbidden('Apenas gestores podem buscar funcionários');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Método não permitido', 405);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) sendValidationError('ID inválido');

try {
    $stmt = $conn->prepare("
        SELECT f.id, f.usuario_id, f.unidade_id, f.cargo, f.status, f.data_contratacao,
               u.nome, u.cpf, u.email, un.nome as unidade_nome
        FROM funcionarios f
        JOIN usuarios u ON f.usuario_id = u.id
        JOIN unidades un ON f.unidade_id = un.id
        WHERE f.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) sendNotFound('Funcionário não encontrado');
    $funcionario = $result->fetch_assoc();
    sendData($funcionario);
} catch (Exception $e) {
    sendInternalError('Erro ao buscar funcionário: ' . $e->getMessage());
}
$conn->close();
?> 