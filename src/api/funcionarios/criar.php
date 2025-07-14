<?php
/**
 * API de Criação de Funcionários - Easy Rake 2.0
 * Cria novos funcionários no sistema
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

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
    $data = validateAndSanitize($input, 'validateEmployeeData');
    
    // Verificar se código de acesso existe e buscar unidade
    $stmt = $conn->prepare("SELECT id, nome FROM unidades WHERE codigo_acesso = ? AND status = 'Ativa'");
    $stmt->bind_param('s', $data['codigo_acesso']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendNotFound('Código de acesso inválido ou unidade inativa');
    }
    
    $unidade = $result->fetch_assoc();
    $unidade_id = $unidade['id'];
    $stmt->close();
    
    // Verificar se CPF já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param('s', $data['cpf']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        sendConflict('CPF já cadastrado no sistema');
    }
    $stmt->close();
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // 1. Criar usuário
        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nome, cpf, email, senha, status, data_criacao) 
            VALUES (?, ?, ?, ?, 'Ativo', NOW())
        ");
        
        $email = $data['cpf'] . '@temp.com'; // Email temporário
        $stmt->bind_param('ssss', 
            $data['nome_completo'],
            $data['cpf'],
            $email,
            $senha_hash
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar usuário: ' . $stmt->error);
        }
        
        $usuario_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Criar vínculo funcionário
        $cargo = ucfirst($data['tipo_usuario']); // Caixa ou Sanger
        
        $stmt = $conn->prepare("
            INSERT INTO funcionarios (usuario_id, unidade_id, cargo, status, data_vinculo) 
            VALUES (?, ?, ?, 'Pendente', NOW())
        ");
        
        $stmt->bind_param('iis', 
            $usuario_id,
            $unidade_id,
            $cargo
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar vínculo de funcionário: ' . $stmt->error);
        }
        
        $funcionario_id = $conn->insert_id;
        $stmt->close();
        
        // 3. Criar solicitação de aprovação
        $stmt = $conn->prepare("
            INSERT INTO aprovacoes_acesso (funcionario_id, tipo, status, data_solicitacao) 
            VALUES (?, ?, 'Pendente', NOW())
        ");
        
        $stmt->bind_param('is', $funcionario_id, $cargo);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar solicitação de aprovação: ' . $stmt->error);
        }
        
        $stmt->close();
        
        // Commit da transação
        $conn->commit();
        
        // Buscar dados do funcionário criado
        $stmt = $conn->prepare("
            SELECT f.id, f.usuario_id, f.unidade_id, f.cargo, f.status, f.data_vinculo,
                   u.nome, u.cpf, u.email
            FROM funcionarios f
            JOIN usuarios u ON f.usuario_id = u.id
            WHERE f.id = ?
        ");
        
        $stmt->bind_param('i', $funcionario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $funcionario = $result->fetch_assoc();
        $stmt->close();
        
        // Resposta de sucesso
        sendCreated('Funcionário cadastrado com sucesso! Aguardando aprovação do gestor.', [
            'funcionario' => $funcionario,
            'unidade' => $unidade['nome']
        ]);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Verificar se é erro de validação
    if (strpos($e->getMessage(), 'Campo obrigatório') !== false || 
        strpos($e->getMessage(), 'As senhas não coincidem') !== false) {
        sendValidationError($e->getMessage());
    }
    
    // Erro interno
    sendInternalError('Erro ao criar funcionário: ' . $e->getMessage());
}

$conn->close();
?> 