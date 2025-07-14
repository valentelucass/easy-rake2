<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.', 'session' => $_SESSION]);
    exit;
}
echo json_encode([
    'success' => true,
    'user_id' => $_SESSION['user_id'] ?? null,
    'nome' => $_SESSION['nome_usuario'] ?? null,
    'perfil' => $_SESSION['perfil'] ?? null,
    'cpf' => $_SESSION['cpf_usuario'] ?? null,
    'session' => $_SESSION
]); 