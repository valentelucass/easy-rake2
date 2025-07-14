<?php
/**
 * API para Listar Funcionários - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageEmployees()) sendForbidden('Apenas gestores podem listar funcionários');

try {
    $sql = "
        SELECT f.id, f.usuario_id, f.unidade_id, f.cargo, f.status, f.data_contratacao,
               u.nome, u.cpf, u.email, un.nome as unidade_nome
        FROM funcionarios f
        JOIN usuarios u ON f.usuario_id = u.id
        JOIN unidades un ON f.unidade_id = un.id
        ORDER BY u.nome
    ";
    $result = $conn->query($sql);
    $funcionarios = [];
    while ($row = $result->fetch_assoc()) {
        $funcionarios[] = $row;
    }
    sendList($funcionarios, count($funcionarios));
} catch (Exception $e) {
    sendInternalError('Erro ao listar funcionários: ' . $e->getMessage());
}
$conn->close();
?> 