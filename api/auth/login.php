<?php
// Inicia a sessão para podermos armazenar os dados do usuário logado
session_start();

// Define o cabeçalho de resposta para JSON
header('Content-Type: application/json');

// Inclui o arquivo de conexão com o banco de dados
require_once '../db_connect.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]);
    exit;
}

try {
    // Pega os dados JSON enviados pelo JavaScript
    $input = json_decode(file_get_contents('php://input'), true);

    $cpf = $input['cpf'] ?? '';
    $senha = $input['senha'] ?? '';

    // Validação básica
    if (empty($cpf) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'CPF e senha são obrigatórios.']);
        exit;
    }

    // Buscar o usuário pelo CPF
    $stmt = $conn->prepare("SELECT id, nome, cpf, senha, perfil, status FROM usuarios WHERE cpf = ?");
    if (!$stmt) {
        throw new Exception('Erro na preparação da query: ' . $conn->error);
    }
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'CPF ou senha inválidos.']);
        exit;
    }

    $user = $result->fetch_assoc();

    // Verificar se o usuário está ativo
    if ($user['status'] !== 'Ativo') {
        echo json_encode(['success' => false, 'message' => 'Sua conta não está ativa. Entre em contato com o administrador.']);
        exit;
    }

    // Verificar a senha
    if (password_verify($senha, $user['senha'])) {
        // Login autorizado! Armazena dados na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome_usuario'] = $user['nome'];
        // Regenera o ID da sessão para previnir session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome_usuario'] = $user['nome'];
        $_SESSION['cpf_usuario'] = $user['cpf'];
        $_SESSION['perfil'] = $user['perfil'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login realizado com sucesso!',
            'user' => [
                'nome' => $user['nome'],
                'perfil' => $user['perfil']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'CPF ou senha inválidos.']);
    }

    $stmt->close();
    // Remover $conn->close(); para evitar erro de conexão já fechada
} catch (Exception $e) {
    // Log do erro detalhado para o administrador do sistema
    error_log('Erro no login: ' . $e->getMessage());
    // Mensagem genérica para o usuário
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.']);
    exit;
}