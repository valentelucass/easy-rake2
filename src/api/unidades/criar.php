<?php
/**
 * API de Criação de Unidades - Easy Rake 2.0
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

// Verificar se é a primeira unidade (permitir criação sem autenticação)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM unidades");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$isFirstUnit = $row['total'] == 0;

// Se não for a primeira unidade, verificar autenticação
if (!$isFirstUnit) {
    if (!isAuthenticated()) sendUnauthorized();
    if (!canManageUnits()) sendForbidden('Apenas gestores podem criar unidades');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Método não permitido', 405);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) sendValidationError('Dados JSON inválidos');
    
    $data = validateAndSanitize($input, 'validateUnitData');
    
    // Gerar código de acesso único
    $codigo_acesso = generateUniqueCode($conn);
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // 1. Criar usuário gestor
        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nome, cpf, email, senha, status, data_criacao) 
            VALUES (?, ?, ?, ?, 'Ativo', NOW())
        ");
        
        $stmt->bind_param('ssss', 
            $data['nome_gestor'],
            $data['cpf_gestor'],
            $data['email_gestor'],
            $senha_hash
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar usuário gestor: ' . $stmt->error);
        }
        
        $usuario_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Criar unidade
        $stmt = $conn->prepare("
            INSERT INTO unidades (nome, telefone, codigo_acesso, status, data_criacao) 
            VALUES (?, ?, ?, 'Ativa', NOW())
        ");
        
        $stmt->bind_param('sss', 
            $data['nome'],
            $data['telefone'],
            $codigo_acesso
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar unidade: ' . $stmt->error);
        }
        
        $unidade_id = $conn->insert_id;
        $stmt->close();
        
        // 3. Criar vínculo funcionário (gestor)
        $stmt = $conn->prepare("
            INSERT INTO funcionarios (usuario_id, unidade_id, cargo, status, data_vinculo, data_aprovacao) 
            VALUES (?, ?, 'Gestor', 'Ativo', NOW(), NOW())
        ");
        
        $stmt->bind_param('ii', $usuario_id, $unidade_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar vínculo de gestor: ' . $stmt->error);
        }
        
        $stmt->close();
        
        // Commit da transação
        $conn->commit();
        
        // Buscar dados da unidade criada
        $stmt = $conn->prepare("SELECT * FROM unidades WHERE id = ?");
        $stmt->bind_param('i', $unidade_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $unidade = $result->fetch_assoc();
        $stmt->close();
        
        // Resposta de sucesso
        $message = $isFirstUnit ? 'Primeira unidade criada com sucesso' : 'Unidade criada com sucesso';
        sendCreated($message, [
            'unidade' => $unidade,
            'gestor_email' => $data['email_gestor'],
            'codigo_acesso' => $codigo_acesso
        ]);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Campo obrigatório') !== false || 
        strpos($e->getMessage(), 'As senhas não coincidem') !== false) {
        sendValidationError($e->getMessage());
    }
    sendInternalError('Erro ao criar unidade: ' . $e->getMessage());
}

$conn->close();

// Função para gerar código de acesso único
function generateUniqueCode($conn) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        $stmt = $conn->prepare("SELECT id FROM unidades WHERE codigo_acesso = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
    } while ($exists);
    
    return $code;
}
?> 