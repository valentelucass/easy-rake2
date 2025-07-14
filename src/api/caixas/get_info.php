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
    if (!isset($_GET["id"])) {
        sendResponse(false, "ID do caixa é obrigatório", 400);
        exit;
    }
    $id = intval($_GET["id"]);
    $sql = "SELECT * FROM caixas WHERE id = ? AND deleted_at IS NULL";
    $stmt = executePreparedQuery($conn, $sql, "i", [$id]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao buscar caixa", 500);
        exit;
    }
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    if (!$data) {
        sendResponse(false, "Caixa não encontrada", 404);
        exit;
    }
    sendResponse(true, "Caixa encontrada", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao buscar caixa: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 