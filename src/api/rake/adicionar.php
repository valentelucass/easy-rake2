<?php
/**
 * API para adicionar rake
 */

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
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        sendResponse(false, "Método não permitido", 405);
        exit;
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || !isset($input["valor"]) || empty($input["caixa_id"])) {
        sendResponse(false, "Dados inválidos", 400);
        exit;
    }
    
    // Registrar rake
    $sql = "INSERT INTO rake (caixa_id, valor, observacao, created_by) VALUES (?, ?, ?, ?)";
    $stmt = executePreparedQuery($conn, $sql, "idsi", [
        $input["caixa_id"],
        $input["valor"],
        $input["observacao"] ?? "",
        $_SESSION["user_id"]
    ]);
    
    if ($stmt === false) {
        sendResponse(false, "Erro ao registrar rake", 500);
        exit;
    }
    
    $id = $conn->insert_id;
    $stmt->close();
    
    sendResponse(true, "Rake registrado com sucesso", 201, ["id" => $id]);
    
} catch (Exception $e) {
    error_log("Erro ao adicionar rake: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
}
?>