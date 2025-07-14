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
    if (!$input || !isset($input["inventario_inicial"])) {
        sendResponse(false, "Inventário inicial é obrigatório", 400);
        exit;
    }
    
    // Obter funcionario_id do usuário logado
    $funcionario_id = getCurrentFuncionarioId();
    if (!$funcionario_id) {
        sendResponse(false, "Funcionário não encontrado", 400);
        exit;
    }
    
    // Obter unidade_id do funcionário
    $unidade_id = getCurrentUnidadeId();
    if (!$unidade_id) {
        sendResponse(false, "Unidade não encontrada", 400);
        exit;
    }
    
    // Verificar se já existe caixa aberto na unidade
    $check_sql = "SELECT id FROM caixas WHERE unidade_id = ? AND status = 'Aberto'";
    $check_stmt = executePreparedQuery($conn, $check_sql, "i", [$unidade_id]);
    if ($check_stmt !== false) {
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $check_stmt->close();
            sendResponse(false, "Já existe um caixa aberto nesta unidade", 400);
            exit;
        }
        $check_stmt->close();
    }
    
    $sql = "INSERT INTO caixas (unidade_id, funcionario_abertura_id, status, inventario_inicial, observacoes) VALUES (?, ?, 'Aberto', ?, ?)";
    $stmt = executePreparedQuery($conn, $sql, "iids", [
        $unidade_id,
        $funcionario_id,
        $input["inventario_inicial"],
        $input["observacoes"] ?? ""
    ]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao criar caixa", 500);
        exit;
    }
    $id = $conn->insert_id;
    $stmt->close();
    sendResponse(true, "Caixa aberto com sucesso", 201, ["id" => $id]);
} catch (Exception $e) {
    error_log("Erro ao criar caixa: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 