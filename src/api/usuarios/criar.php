<?php
/**
 * API de Criação de Usuários - Easy Rake 2.0
 * Cria novos usuários no sistema
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

// Verificar se é o primeiro usuário (permitir criação sem autenticação)
$isFirstUser = isFirstUser();

// Se não for o primeiro usuário, verificar autenticação
if (!$isFirstUser) {
    if (!isAuthenticated()) {
        sendUnauthorized();
    }
    
    // Verificar permissões (apenas gestores podem criar usuários)
    if (!canManageUsers()) {
        sendForbidden('Apenas gestores podem criar usuários');
    }
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Método não permitido', 405);
}

try {
    // Obter dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendValidationError('Dados JSON inválidos');
    }
    
    // Validar e sanitizar dados
    $data = validateAndSanitize($input, 'validateUserData');
    
    // Verificar se CPF já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param('s', $data['cpf']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        sendConflict('CPF já cadastrado no sistema');
    }
    $stmt->close();
    
    // Verificar se email já existe
    if (isset($data['email'])) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            sendConflict('Email já cadastrado no sistema');
        }
        $stmt->close();
    }
    
    // Hash da senha
    $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
    
    // Inserir usuário (sem perfil - está na tabela funcionarios)
    $stmt = $conn->prepare("
        INSERT INTO usuarios (nome, cpf, email, senha, status, data_criacao) 
        VALUES (?, ?, ?, ?, 'Ativo', NOW())
    ");
    
    $stmt->bind_param('ssss', 
        $data['nome'],
        $data['cpf'],
        $data['email'],
        $senha_hash
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao criar usuário: ' . $stmt->error);
    }
    
    $usuario_id = $conn->insert_id;
    $stmt->close();
    
    // Se for o primeiro usuário, criar vínculo como gestor na primeira unidade
    if ($isFirstUser) {
        // Buscar primeira unidade
        $stmt = $conn->prepare("SELECT id FROM unidades ORDER BY id LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $unidade = $result->fetch_assoc();
        $stmt->close();
        
        if ($unidade) {
            // Criar vínculo como gestor
            $stmt = $conn->prepare("
                INSERT INTO funcionarios (usuario_id, unidade_id, cargo, status, data_vinculo, data_aprovacao) 
                VALUES (?, ?, 'Gestor', 'Ativo', NOW(), NOW())
            ");
            
            $stmt->bind_param('ii', $usuario_id, $unidade['id']);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Buscar dados do usuário criado (sem senha)
    $stmt = $conn->prepare("
        SELECT id, nome, cpf, email, status, data_criacao 
        FROM usuarios 
        WHERE id = ?
    ");
    
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    // Log da operação (apenas se não for o primeiro usuário)
    if (!$isFirstUser) {
        $funcionario_id = getCurrentFuncionarioId();
        $log_stmt = $conn->prepare("
            INSERT INTO relatorios_historico (funcionario_id, unidade_id, tipo, status, data_geracao, observacoes)
            VALUES (?, ?, 'Usuarios', 'Gerado', NOW(), ?)
        ");
        
        $observacao = "Usuário criado: " . $usuario['nome'] . " (ID: " . $usuario_id . ")";
        $log_stmt->bind_param('iis', $funcionario_id, getCurrentUnidadeId(), $observacao);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    // Resposta de sucesso
    $message = $isFirstUser ? 'Primeiro usuário gestor criado com sucesso' : 'Usuário criado com sucesso';
    sendCreated($message, $usuario);
    
} catch (Exception $e) {
    // Verificar se é erro de validação
    if (strpos($e->getMessage(), 'Campo obrigatório') !== false || 
        strpos($e->getMessage(), 'deve') !== false) {
        sendValidationError($e->getMessage());
    }
    
    // Erro interno
    sendInternalError('Erro ao criar usuário: ' . $e->getMessage());
}

$conn->close();
?> 