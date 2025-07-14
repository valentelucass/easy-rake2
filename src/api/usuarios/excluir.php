<?php
/**
 * API para Excluir Usuário - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageUsers()) sendForbidden('Apenas gestores podem excluir usuários');
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') sendError('Método não permitido', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id'])) sendValidationError('Dados inválidos');
$id = intval($input['id']);
if ($id <= 0) sendValidationError('ID inválido');

try {
    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    sendDeleted('Usuário excluído com sucesso');
} catch (Exception $e) {
    sendInternalError('Erro ao excluir usuário: ' . $e->getMessage());
}
$conn->close();
?> 