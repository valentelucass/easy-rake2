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
    $sql = "SELECT * FROM relatorios WHERE deleted_at IS NULL ORDER BY created_at DESC";
    $result = executeQuery($conn, $sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    sendResponse(true, "Histórico de relatórios carregado", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao carregar histórico: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 