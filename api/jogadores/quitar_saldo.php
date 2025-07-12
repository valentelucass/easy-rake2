<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

require_once '../db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$jogador_id = intval($data['jogador_id'] ?? 0);
$tipo_quita = $data['tipo_quita'] ?? ''; // 'debito' ou 'credito'
$valor = floatval($data['valor'] ?? 0);
$observacao = trim($data['observacao'] ?? '');

if ($jogador_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do jogador inválido.']);
    exit;
}

if (!in_array($tipo_quita, ['debito', 'credito'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de quitação inválido.']);
    exit;
}

if ($valor <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valor deve ser maior que zero.']);
    exit;
}

try {
    // Busca dados do jogador
    $stmt = $conn->prepare('SELECT * FROM jogadores WHERE id = ?');
    $stmt->bind_param('i', $jogador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Jogador não encontrado.']);
        exit;
    }
    
    $jogador = $result->fetch_assoc();
    $saldo_atual = floatval($jogador['saldo_atual']);
    $novo_saldo = $saldo_atual;
    
    // Calcula novo saldo baseado no tipo de quitação
    if ($tipo_quita === 'debito') {
        // Jogador paga um débito (reduz saldo negativo)
        if ($saldo_atual >= 0) {
            echo json_encode(['success' => false, 'message' => 'Jogador não possui débito para quitar.']);
            exit;
        }
        $novo_saldo = min(0, $saldo_atual + $valor);
    } else {
        // Caixa paga crédito ao jogador (reduz saldo positivo)
        if ($saldo_atual <= 0) {
            echo json_encode(['success' => false, 'message' => 'Jogador não possui crédito para quitar.']);
            exit;
        }
        $novo_saldo = max(0, $saldo_atual - $valor);
    }
    
    // Inicia transação
    $conn->begin_transaction();
    
    try {
        // Atualiza saldo do jogador
        $stmt = $conn->prepare('UPDATE jogadores SET saldo_atual = ? WHERE id = ?');
        $stmt->bind_param('di', $novo_saldo, $jogador_id);
        $stmt->execute();
        
        // Registra a transação
        $operador_id = $_SESSION['user_id'];
        $tipo_transacao = $tipo_quita === 'debito' ? 'ACERTO_POSITIVO' : 'ACERTO_NEGATIVO';
        $valor_transacao = $tipo_quita === 'debito' ? $valor : -$valor;
        
        $stmt = $conn->prepare('INSERT INTO transacoes_jogadores (jogador_id, operador_id, tipo, valor, observacao, quitado) VALUES (?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('iisds', $jogador_id, $operador_id, $tipo_transacao, $valor_transacao, $observacao);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Quitação realizada com sucesso!',
            'saldo_anterior' => $saldo_atual,
            'novo_saldo' => $novo_saldo,
            'valor_quitado' => $valor
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Erro ao realizar quitação: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro interno ao processar a quitação.']);
}

$conn->close();
?>