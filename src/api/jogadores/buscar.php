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
    $nome = isset($_GET["nome"]) ? $_GET["nome"] : '';
    $sql = "SELECT * FROM jogadores WHERE nome LIKE ? AND deleted_at IS NULL ORDER BY created_at DESC";
    $stmt = executePreparedQuery($conn, $sql, "s", ["%$nome%"]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao buscar jogadores", 500);
        exit;
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    sendResponse(true, "Jogadores encontrados", 200, $data);
} catch (Exception $e) {
    error_log("Erro ao buscar jogadores: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 