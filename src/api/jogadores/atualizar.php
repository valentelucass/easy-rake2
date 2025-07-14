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
    
    // Verificar se CPF já existe na unidade (se foi alterado)
    if (!empty($input["cpf"])) {
        $check_sql = "SELECT id FROM jogadores WHERE cpf = ? AND unidade_id = ? AND id != ?";
        $check_stmt = executePreparedQuery($conn, $check_sql, "sii", [$input["cpf"], $unidade_id, $input["id"]]);
        if ($check_stmt !== false) {
            $result = $check_stmt->get_result();
            if ($result->num_rows > 0) {
                $check_stmt->close();
                sendResponse(false, "CPF já cadastrado nesta unidade", 400);
                exit;
            }
            $check_stmt->close();
        }
    }
    
    $sql = "UPDATE jogadores SET nome = ?, cpf = ?, telefone = ?, email = ?, limite_credito = ?, saldo_atual = ?, status = ? WHERE id = ? AND unidade_id = ?";
    $stmt = executePreparedQuery($conn, $sql, "ssssddsii", [
        $input["nome"] ?? "",
        $input["cpf"] ?? "",
        $input["telefone"] ?? null,
        $input["email"] ?? null,
        $input["limite_credito"] ?? 0.00,
        $input["saldo_atual"] ?? 0.00,
        $input["status"] ?? "Ativo",
        $input["id"],
        $unidade_id
    ]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao atualizar jogador", 500);
        exit;
    }
    if ($stmt->affected_rows === 0) {
        sendResponse(false, "Jogador não encontrado", 404);
        exit;
    }
    $stmt->close();
    sendResponse(true, "Jogador atualizado com sucesso", 200);
} catch (Exception $e) {
    error_log("Erro ao atualizar jogador: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 