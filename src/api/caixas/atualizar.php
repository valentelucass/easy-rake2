<?php
require_once "../db_connect.php";
require_once "../utils/auth.php";
require_once "../utils/response.php";
require_once "../utils/helpers.php";

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
    
    // Obter unidade_id do funcionário logado
    $unidade_id = getCurrentUnidadeId();
    if (!$unidade_id) {
        sendResponse(false, "Unidade não encontrada", 400);
        exit;
    }
    
    // Verificar se o caixa pertence à unidade do funcionário
    $check_sql = "SELECT id FROM caixas WHERE id = ? AND unidade_id = ?";
    $check_stmt = executePreparedQuery($conn, $check_sql, "ii", [$input["id"], $unidade_id]);
    if ($check_stmt === false || $check_stmt->get_result()->num_rows === 0) {
        if ($check_stmt !== false) $check_stmt->close();
        sendResponse(false, "Caixa não encontrado", 404);
        exit;
    }
    $check_stmt->close();
    
    // Atualizar apenas campos permitidos
    $update_fields = [];
    $params = [];
    $types = "";
    
    if (isset($input["inventario_final"])) {
        $update_fields[] = "inventario_final = ?";
        $params[] = $input["inventario_final"];
        $types .= "d";
    }
    
    if (isset($input["status"])) {
        $update_fields[] = "status = ?";
        $params[] = $input["status"];
        $types .= "s";
    }
    
    if (isset($input["observacoes"])) {
        $update_fields[] = "observacoes = ?";
        $params[] = $input["observacoes"];
        $types .= "s";
    }
    
    if (isset($input["status"]) && $input["status"] === "Fechado") {
        $update_fields[] = "funcionario_fechamento_id = ?";
        $update_fields[] = "data_fechamento = NOW()";
        $funcionario_id = getCurrentFuncionarioId();
        $params[] = $funcionario_id;
        $types .= "i";
    }
    
    if (empty($update_fields)) {
        sendResponse(false, "Nenhum campo válido para atualizar", 400);
        exit;
    }
    
    $params[] = $input["id"];
    $types .= "i";
    
    $sql = "UPDATE caixas SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = executePreparedQuery($conn, $sql, $types, $params);
    
    if ($stmt === false) {
        sendResponse(false, "Erro ao atualizar caixa", 500);
        exit;
    }
    if ($stmt->affected_rows === 0) {
        sendResponse(false, "Caixa não encontrado", 404);
        exit;
    }
    $stmt->close();
    sendResponse(true, "Caixa atualizado com sucesso", 200);
} catch (Exception $e) {
    error_log("Erro ao atualizar caixa: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 