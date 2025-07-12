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

// Atualização de jogador via PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $data = json_decode(file_get_contents('php://input'), true);
    $nome = trim($data['nome'] ?? '');
    $cpf = trim($data['cpf'] ?? '');
    $telefone = trim($data['telefone'] ?? '');
    $limite_credito = isset($data['limite_credito']) ? floatval($data['limite_credito']) : 0.00;

    function validarCPF($cpf) {
        return preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf);
    }
    if (empty($nome) || empty($cpf)) {
        echo json_encode(['success' => false, 'message' => 'Nome e CPF são obrigatórios.']);
        exit;
    }
    if (!validarCPF($cpf)) {
        echo json_encode(['success' => false, 'message' => 'CPF em formato inválido.']);
        exit;
    }
    // Verifica se o CPF já existe em outro jogador
    $stmt = $conn->prepare('SELECT id FROM jogadores WHERE cpf = ? AND id != ?');
    $stmt->bind_param('si', $cpf, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'CPF já cadastrado em outro jogador.']);
        $stmt->close();
        exit;
    }
    $stmt->close();
    // Atualiza os dados
    $stmt = $conn->prepare('UPDATE jogadores SET nome = ?, cpf = ?, telefone = ?, limite_credito = ? WHERE id = ?');
    $stmt->bind_param('sssdi', $nome, $cpf, $telefone, $limite_credito, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Jogador atualizado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar jogador.']);
    }
    $stmt->close();
    // $conn->close();
    exit;
}

// Pega os parâmetros de busca
$busca = $_GET['busca'] ?? '';
$status = $_GET['status'] ?? '';
$limite = $_GET['limite'] ?? 10;

// Se for busca por ID específico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    // Busca dados do jogador
    $stmt = $conn->prepare('SELECT * FROM jogadores WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Busca histórico de transações não quitadas
        $transacoes = [];
        $stmt2 = $conn->prepare('SELECT t.*, u.nome as operador_nome FROM transacoes_jogadores t LEFT JOIN usuarios u ON t.operador_id = u.id WHERE t.jogador_id = ? AND t.quitado = 0 ORDER BY t.data_transacao DESC');
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        while ($t = $result2->fetch_assoc()) {
            $transacoes[] = [
                'id' => $t['id'],
                'tipo' => $t['tipo'],
                'valor' => (float)$t['valor'],
                'observacao' => $t['observacao'],
                'quitado' => (bool)$t['quitado'],
                'data_transacao' => $t['data_transacao'],
                'operador_nome' => $t['operador_nome']
            ];
        }
        $stmt2->close();
        echo json_encode([
            'success' => true,
            'jogador' => [
                'id' => $row['id'],
                'nome' => $row['nome'],
                'cpf' => $row['cpf'],
                'telefone' => $row['telefone'],
                'email' => $row['email'],
                'status' => $row['status'],
                'data_cadastro' => $row['data_cadastro'],
                'limite_credito' => isset($row['limite_credito']) ? (float)$row['limite_credito'] : 0.00,
                'saldo_atual' => isset($row['saldo_atual']) ? (float)$row['saldo_atual'] : 0.00
            ],
            'transacoes' => $transacoes
        ]);
        $stmt->close();
        // $conn->close();
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Jogador não encontrado.']);
        $stmt->close();
        // $conn->close();
        exit;
    }
}

$user_id = $_SESSION['user_id'];

try {
    // Constrói a query base
    $query = "SELECT * FROM jogadores";
    $params = [];
    $types = "";
    
    // Adiciona filtros se fornecidos
    if (!empty($busca)) {
        $query .= " AND (nome LIKE ? OR cpf LIKE ? OR telefone LIKE ?)";
        $busca_param = "%$busca%";
        $params[] = $busca_param;
        $params[] = $busca_param;
        $params[] = $busca_param;
        $types .= "sss";
    }
    
    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY data_cadastro DESC LIMIT ?";
    $params[] = (int)$limite;
    $types .= "i";
    
    // Prepara e executa a query
    $stmt = $conn->prepare($query);
    
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $jogadores = [];
    while ($row = $result->fetch_assoc()) {
        $jogadores[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'cpf' => $row['cpf'],
            'telefone' => $row['telefone'],
            'email' => $row['email'],
            'status' => $row['status'],
            'data_cadastro' => $row['data_cadastro'],
            'limite_credito' => isset($row['limite_credito']) ? (float)$row['limite_credito'] : 0.00,
            'saldo_atual' => isset($row['saldo_atual']) ? (float)$row['saldo_atual'] : 0.00
        ];
    }
    
    echo json_encode([
        'success' => true,
        'jogadores' => $jogadores,
        'total' => count($jogadores)
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar jogadores.']);
}

// $conn->close();
?> 