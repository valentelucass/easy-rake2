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
    if (!$input || empty($input["nome"]) || empty($input["cpf"])) {
        sendResponse(false, "Nome e CPF são obrigatórios", 400);
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
    
    // Verificar se CPF já existe na unidade
    $check_sql = "SELECT id FROM jogadores WHERE cpf = ? AND unidade_id = ?";
    $check_stmt = executePreparedQuery($conn, $check_sql, "si", [$input["cpf"], $unidade_id]);
    if ($check_stmt !== false) {
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $check_stmt->close();
            sendResponse(false, "CPF já cadastrado nesta unidade", 400);
            exit;
        }
        $check_stmt->close();
    }
    
    $sql = "INSERT INTO jogadores (unidade_id, nome, cpf, telefone, email, limite_credito, saldo_atual, funcionario_cadastro_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = executePreparedQuery($conn, $sql, "isssddi", [
        $unidade_id,
        $input["nome"],
        $input["cpf"],
        $input["telefone"] ?? null,
        $input["email"] ?? null,
        $input["limite_credito"] ?? 0.00,
        $input["saldo_atual"] ?? 0.00,
        $funcionario_id
    ]);
    if ($stmt === false) {
        sendResponse(false, "Erro ao criar jogador", 500);
        exit;
    }
    $id = $conn->insert_id;
    $stmt->close();
    sendResponse(true, "Jogador criado com sucesso", 201, ["id" => $id]);
} catch (Exception $e) {
    error_log("Erro ao criar jogador: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
} 