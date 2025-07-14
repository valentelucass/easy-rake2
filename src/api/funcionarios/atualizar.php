<?php
/**
 * API para Atualizar Funcionário - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageEmployees()) sendForbidden('Apenas gestores podem atualizar funcionários');
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') sendError('Método não permitido', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id'])) sendValidationError('Dados inválidos');
$id = intval($input['id']);
if ($id <= 0) sendValidationError('ID inválido');

try {
    $fields = [];
    $params = [];
    $types = '';
    if (isset($input['cargo'])) { $fields[] = 'cargo = ?'; $params[] = $input['cargo']; $types .= 's'; }
    if (isset($input['status'])) { $fields[] = 'status = ?'; $params[] = $input['status']; $types .= 's'; }
    if (isset($input['unidade_id'])) { $fields[] = 'unidade_id = ?'; $params[] = $input['unidade_id']; $types .= 'i'; }
    if (empty($fields)) sendValidationError('Nenhum campo para atualizar');
    $params[] = $id; $types .= 'i';
    $sql = 'UPDATE funcionarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    sendUpdated('Funcionário atualizado com sucesso');
} catch (Exception $e) {
    sendInternalError('Erro ao atualizar funcionário: ' . $e->getMessage());
}
$conn->close();
?> 