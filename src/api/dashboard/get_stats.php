<?php
require_once "../db_connect.php";
require_once "../utils/auth.php";
require_once "../utils/response.php";

if (!isAuthenticated()) {
    sendResponse(false, "Não autorizado", 401);
    exit;
}

$conn = getConnection();
if (!$conn) {
    sendResponse(false, "Erro de conexão com banco", 500);
    exit;
}

try {
    $sql = "SELECT COUNT(*) as total_usuarios FROM usuarios WHERE deleted_at IS NULL";
    $result = executeQuery($conn, $sql);
    $data = $result->fetch_assoc();
    sendResponse(true, "Estatísticas carregadas", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao carregar estatísticas: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 