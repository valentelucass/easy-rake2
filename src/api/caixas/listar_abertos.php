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
    error_log('[DEBUG] Erro de conexão com banco em listar_abertos');
    sendResponse(false, "Erro de conexão com banco", 500);
    exit;
}

try {
    $sql = "SELECT * FROM caixas WHERE status = 'Aberto' AND deleted_at IS NULL ORDER BY data_abertura DESC";
    error_log('[DEBUG] Query listar_abertos: ' . $sql);
    $result = executeQuery($conn, $sql);
    if ($result === false) {
        error_log('[DEBUG] Erro ao executar query listar_abertos: ' . $conn->error);
        sendResponse(false, "Erro ao buscar caixas abertas", 500);
        exit;
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    sendResponse(true, "Caixas abertas listadas com sucesso", 200, $data);
} catch (Exception $e) {
    error_log("[DEBUG] Exceção ao listar caixas abertas: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 