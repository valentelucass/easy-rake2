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
    // Exemplo: total de caixas, jogadores, gastos
    $sql = "SELECT (SELECT COUNT(*) FROM caixas WHERE deleted_at IS NULL) AS total_caixas, (SELECT COUNT(*) FROM jogadores WHERE deleted_at IS NULL) AS total_jogadores, (SELECT SUM(valor) FROM gastos WHERE deleted_at IS NULL) AS total_gastos";
    $result = executeQuery($conn, $sql);
    $data = $result->fetch_assoc();
    sendResponse(true, "Dashboard carregado", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao carregar dashboard: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 