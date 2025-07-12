<?php
session_start();
require_once '../db_connect.php';
require_once '../utils/response.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    send_json_response(false, 'Usuário não autenticado');
    exit;
}

// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Método não permitido');
    exit;
}

// Verificar permissão (apenas Gestor e Caixa)
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    send_json_response(false, 'Acesso negado. Apenas Gestores e Caixas podem registrar despesas.');
    exit;
}

try {
    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        send_json_response(false, 'Dados inválidos');
        exit;
    }

    $descricao = trim($input['descricao'] ?? '');
    $valor = floatval($input['valor'] ?? 0);
    $observacoes = trim($input['observacoes'] ?? '');

    // Validações
    if (empty($descricao)) {
        send_json_response(false, 'Descrição é obrigatória');
        exit;
    }

    if ($valor <= 0) {
        send_json_response(false, 'Valor deve ser maior que zero');
        exit;
    }

    // Verificar se existe um caixa aberto para o usuário
    $stmt = $conn->prepare("
        SELECT id FROM caixas 
        WHERE operador_id = ? AND status = 'Aberto' 
        ORDER BY data_abertura DESC 
        LIMIT 1
    ");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $caixa = $result->fetch_assoc();

    if (!$caixa) {
        send_json_response(false, 'Nenhum caixa aberto encontrado. Abra um caixa antes de registrar despesas.');
        exit;
    }

    // Inserir o gasto
    $stmt = $conn->prepare("
        INSERT INTO gastos (caixa_id, descricao, valor, observacoes, operador_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param('isdsi', $caixa['id'], $descricao, $valor, $observacoes, $_SESSION['user_id']);
    $stmt->execute();

    $gasto_id = $conn->insert_id;

    // Buscar dados completos do gasto para retorno
    $stmt = $conn->prepare("
        SELECT g.*, u.nome as operador, u.perfil
        FROM gastos g
        JOIN usuarios u ON g.operador_id = u.id
        WHERE g.id = ?
    ");
    $stmt->bind_param('i', $gasto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $gasto = $result->fetch_assoc();

    send_json_response(true, 'Despesa registrada com sucesso', $gasto);

} catch (Exception $e) {
    error_log("Erro ao registrar gasto: " . $e->getMessage());
    send_json_response(false, 'Erro interno do servidor');
} catch (Exception $e) {
    error_log("Erro inesperado ao registrar gasto: " . $e->getMessage());
    send_json_response(false, 'Erro inesperado');
}
?>