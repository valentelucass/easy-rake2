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
    if (!$input || empty($input["id"]) || !isset($input["valor"])) {
        sendResponse(false, "ID e valor são obrigatórios", 400);
        exit;
    }
    $sql = "UPDATE jogadores SET saldo = saldo - ? WHERE id = ? AND deleted_at IS NULL";
    $stmt = executePreparedQuery($conn, $sql, "di", [
        $input["valor"],
        $input["id"]
    ]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao quitar saldo", 500);
        exit;
    }
    if ($stmt->affected_rows === 0) {
        sendResponse(false, "Jogador não encontrado ou saldo não alterado", 404);
        exit;
    }
    $stmt->close();
    sendResponse(true, "Saldo quitado com sucesso", 200);
} catch (Exception $e) {
    error_log("Erro ao quitar saldo: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 