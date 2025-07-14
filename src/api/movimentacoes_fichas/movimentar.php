<?php
/**
 * API para movimentação de fichas
 */

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
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        sendResponse(false, "Método não permitido", 405);
        exit;
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || empty($input["caixa_id"]) || empty($input["ficha_denom_id"]) || 
        !isset($input["quantidade"]) || empty($input["tipo"])) {
        sendResponse(false, "Dados inválidos: caixa_id, ficha_denom_id, quantidade e tipo são obrigatórios", 400);
        exit;
    }
    
    // Obter funcionario_id do usuário logado
    $funcionario_id = getCurrentFuncionarioId();
    if (!$funcionario_id) {
        sendResponse(false, "Funcionário não encontrado", 400);
        exit;
    }
    
    // Verificar se o tipo é válido
    $tipos_validos = ['COMPRA', 'DEVOLUCAO', 'RAKE', 'CAIXINHA', 'TRANSFERENCIA'];
    if (!in_array($input["tipo"], $tipos_validos)) {
        sendResponse(false, "Tipo de movimentação inválido", 400);
        exit;
    }
    
    // Verificar se o caixa existe e está aberto
    $check_caixa_sql = "SELECT id FROM caixas WHERE id = ? AND status = 'Aberto'";
    $check_caixa_stmt = executePreparedQuery($conn, $check_caixa_sql, "i", [$input["caixa_id"]]);
    if ($check_caixa_stmt === false || $check_caixa_stmt->get_result()->num_rows === 0) {
        if ($check_caixa_stmt !== false) $check_caixa_stmt->close();
        sendResponse(false, "Caixa não encontrado ou não está aberto", 400);
        exit;
    }
    $check_caixa_stmt->close();
    
    // Verificar se a denominação de ficha existe
    $check_ficha_sql = "SELECT valor FROM fichas_denom WHERE id = ? AND status = 'Ativo'";
    $check_ficha_stmt = executePreparedQuery($conn, $check_ficha_sql, "i", [$input["ficha_denom_id"]]);
    if ($check_ficha_stmt === false || $check_ficha_stmt->get_result()->num_rows === 0) {
        if ($check_ficha_stmt !== false) $check_ficha_stmt->close();
        sendResponse(false, "Denominação de ficha não encontrada", 400);
        exit;
    }
    $ficha_result = $check_ficha_stmt->get_result();
    $ficha_data = $ficha_result->fetch_assoc();
    $check_ficha_stmt->close();
    
    // Calcular valor total
    $valor_total = $ficha_data['valor'] * $input["quantidade"];
    
    // Registrar movimentação
    $sql = "INSERT INTO movimentacoes_fichas (caixa_id, jogador_id, funcionario_id, tipo, ficha_denom_id, quantidade, valor_total, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = executePreparedQuery($conn, $sql, "iiisidss", [
        $input["caixa_id"],
        $input["jogador_id"] ?? null,
        $funcionario_id,
        $input["tipo"],
        $input["ficha_denom_id"],
        $input["quantidade"],
        $valor_total,
        $input["observacoes"] ?? ""
    ]);
    
    if ($stmt === false) {
        sendResponse(false, "Erro ao registrar movimentação", 500);
        exit;
    }
    
    $stmt->close();
    
    sendResponse(true, "Movimentação registrada com sucesso", 201, [
        "valor_total" => $valor_total,
        "valor_unitario" => $ficha_data['valor']
    ]);
    
} catch (Exception $e) {
    error_log("Erro na movimentação de fichas: " . $e->getMessage());
    sendResponse(false, "Erro interno do servidor", 500);
} finally {
    closeConnection($conn);
}
?>