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
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || empty($input["id"])) {
        sendResponse(false, "ID é obrigatório", 400);
        exit;
    }
    $sql = "UPDATE jogadores SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
    $stmt = executePreparedQuery($conn, $sql, "i", [$input["id"]]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao excluir jogador", 500);
        exit;
    }
    if ($stmt->affected_rows === 0) {
        sendResponse(false, "Jogador não encontrado", 404);
        exit;
    }
    $stmt->close();
    sendResponse(true, "Jogador excluído com sucesso", 200);
} catch (Exception $e) {
    error_log("Erro ao excluir jogador: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 