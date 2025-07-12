<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
$nome = htmlspecialchars(trim($data['nome'] ?? ''));
$cpf = htmlspecialchars(trim($data['cpf'] ?? ''));
$telefone = htmlspecialchars(trim($data['telefone'] ?? ''));
$limite_credito = isset($data['limite_credito']) ? floatval($data['limite_credito']) : 0.00;

// Função simples para validar CPF (formato ###.###.###-##)
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

// Verifica se o CPF já existe
$stmt = $conn->prepare('SELECT id FROM jogadores WHERE cpf = ?');
$stmt->bind_param('s', $cpf);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'CPF já cadastrado.']);
    $stmt->close();
    exit;
}
$stmt->close();

// Insere o novo jogador
$stmt = $conn->prepare('INSERT INTO jogadores (nome, cpf, telefone, limite_credito, saldo_atual, status) VALUES (?, ?, ?, ?, 0.00, "Ativo")');
$stmt->bind_param('sssd', $nome, $cpf, $telefone, $limite_credito);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Jogador cadastrado com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar jogador.']);
}
$stmt->close();
$conn->close();
exit;