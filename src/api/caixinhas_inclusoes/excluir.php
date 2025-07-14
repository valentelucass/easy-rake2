<?php
/**
 * API CRUD para caixinhas
 * Método: excluir_inclusao
 */

require_once "../db_connect.php";
require_once "../utils/auth.php";
require_once "../utils/response.php";

// Verificar autenticação
if (!isAuthenticated()) {
    sendResponse(false, "Não autorizado", 401);
    exit;
}

// Verificar permissões
if (!hasPermission("manage_caixinhas")) {
    sendResponse(false, "Permissão negada", 403);
    exit;
}

$conn = getConnection();
if (!$conn) {
    sendResponse(false, "Erro de conexão com banco", 500);
    exit;
}

try {
    $method = $_SERVER["REQUEST_METHOD"];
    
    switch ($method) {
        case "GET":
            // Listar caixinhas
            $sql = "SELECT * FROM caixinhas WHERE deleted_at IS NULL ORDER BY created_at DESC";
            $result = executeQuery($conn, $sql);
            
            if ($result === false) {
                sendResponse(false, "Erro ao buscar caixinhas", 500);
                exit;
            }
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            sendResponse(true, "caixinhas listados com sucesso", 200, $data);
            break;
            
        case "POST":
            // Criar caixinhas
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input) {
                sendResponse(false, "Dados inválidos", 400);
                exit;
            }
            
            // Validação básica
            if (empty($input["nome"])) {
                sendResponse(false, "Nome é obrigatório", 400);
                exit;
            }
            
            $sql = "INSERT INTO caixinhas (nome, descricao, created_by) VALUES (?, ?, ?)";
            $stmt = executePreparedQuery($conn, $sql, "ssi", [
                $input["nome"],
                $input["descricao"] ?? "",
                $_SESSION["user_id"]
            ]);
            
            if ($stmt === false) {
                sendResponse(false, "Erro ao criar caixinhas", 500);
                exit;
            }
            
            $id = $conn->insert_id;
            $stmt->close();
            
            // Log da operação
            logOperation("CREATE", "caixinhas", $id, $_SESSION["user_id"]);
            
            sendResponse(true, "caixinhas criado com sucesso", 201, ["id" => $id]);
            break;
            
        case "PUT":
            // Atualizar caixinhas
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input || empty($input["id"])) {
                sendResponse(false, "ID é obrigatório", 400);
                exit;
            }
            
            $sql = "UPDATE caixinhas SET nome = ?, descricao = ?, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = executePreparedQuery($conn, $sql, "ssi", [
                $input["nome"] ?? "",
                $input["descricao"] ?? "",
                $input["id"]
            ]);
            
            if ($stmt === false) {
                sendResponse(false, "Erro ao atualizar caixinhas", 500);
                exit;
            }
            
            if ($stmt->affected_rows === 0) {
                sendResponse(false, "caixinhas não encontrado", 404);
                exit;
            }
            
            $stmt->close();
            
            // Log da operação
            logOperation("UPDATE", "caixinhas", $input["id"], $_SESSION["user_id"]);
            
            sendResponse(true, "caixinhas atualizado com sucesso", 200);
            break;
            
        case "DELETE":
            // Excluir caixinhas (soft delete)
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input || empty($input["id"])) {
                sendResponse(false, "ID é obrigatório", 400);
                exit;
            }
            
            $sql = "UPDATE caixinhas SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = executePreparedQuery($conn, $sql, "i", [$input["id"]]);
            
            if ($stmt === false) {
                sendResponse(false, "Erro ao excluir caixinhas", 500);
                exit;
            }
            
            if ($stmt->affected_rows === 0) {
                sendResponse(false, "caixinhas não encontrado", 404);
                exit;
            }
            
            $stmt->close();
            
            // Log da operação
            logOperation("DELETE", "caixinhas", $input["id"], $_SESSION["user_id"]);
            
            sendResponse(true, "caixinhas excluído com sucesso", 200);
            break;
            
        default:
            sendResponse(false, "Método não permitido", 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API caixinhas: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
}
?>