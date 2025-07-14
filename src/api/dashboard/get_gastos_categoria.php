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
    $sql = "SELECT categoria, SUM(valor) as total FROM gastos WHERE deleted_at IS NULL GROUP BY categoria ORDER BY total DESC";
    $result = executeQuery($conn, $sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    sendResponse(true, "Gastos por categoria carregados", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao carregar gastos por categoria: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 