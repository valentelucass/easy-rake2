<?php
// Inicia a sessão
session_start();

// Define o cabeçalho de resposta para JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// Inclui a conexão com o banco de dados
require_once '../db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]));
}

// Pega os dados JSON enviados pelo JavaScript
$input = json_decode(file_get_contents('php://input'), true);

$valor_inicial = $input['valor_inicial'] ?? 0;
$observacoes = $input['observacoes'] ?? '';
$operador_id = $_SESSION['user_id'];

// Validação básica
if (empty($valor_inicial) || $valor_inicial <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valor inicial é obrigatório e deve ser maior que zero.']);
    exit;
}

try {
    // Verifica se já existe um caixa aberto para este operador
    $checkStmt = $conn->prepare("SELECT id FROM caixas WHERE operador_id = ? AND status = 'Aberto'");
    $checkStmt->bind_param("i", $operador_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Você já possui um caixa aberto.']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    // Prepara a query para inserir o novo caixa
    $stmt = $conn->prepare("INSERT INTO caixas (operador_id, valor_inicial, observacoes, status, data_abertura) VALUES (?, ?, ?, 'Aberto', NOW())");
    $stmt->bind_param("ids", $operador_id, $valor_inicial, $observacoes);
    
    if ($stmt->execute()) {
        $caixa_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Caixa aberto com sucesso!',
            'caixa_id' => $caixa_id,
            'valor_inicial' => $valor_inicial
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao abrir caixa.']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}

//$conn->close();
?>