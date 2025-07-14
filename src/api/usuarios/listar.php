<?php
/**
 * API para Listar Usuários - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../../db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageUsers()) sendForbidden('Apenas gestores podem listar usuários');

try {
    $result = $conn->query("SELECT id, nome, cpf, email, status, data_criacao FROM usuarios");
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    sendList($usuarios, count($usuarios));
} catch (Exception $e) {
    sendInternalError('Erro ao listar usuários: ' . $e->getMessage());
}
$conn->close();
?> 