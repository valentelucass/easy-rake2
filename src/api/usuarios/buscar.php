<?php
/**
 * API para Buscar Usuário por ID - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageUsers()) sendForbidden('Apenas gestores podem buscar usuários');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') sendError('Método não permitido', 405);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) sendValidationError('ID inválido');

try {
    $stmt = $conn->prepare("SELECT id, nome, cpf, email, status, data_criacao FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) sendNotFound('Usuário não encontrado');
    $usuario = $result->fetch_assoc();
    sendData($usuario);
} catch (Exception $e) {
    sendInternalError('Erro ao buscar usuário: ' . $e->getMessage());
}
$conn->close();
?> 