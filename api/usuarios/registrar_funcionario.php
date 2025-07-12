<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

// Padronizar CPF do funcionário (apenas números)
if (isset($input['cpf'])) {
    $input['cpf'] = preg_replace('/\D/', '', $input['cpf']);
}

// Validação básica
$required_fields = ['codigo_acesso', 'nome_completo', 'cpf', 'senha', 'confirmar_senha', 'tipo_usuario'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "O campo '$field' é obrigatório."]);
        exit;
    }
}
if ($input['senha'] !== $input['confirmar_senha']) {
    echo json_encode(['success' => false, 'message' => 'As senhas não coincidem.']);
    exit;
}
// Validação do tipo_usuario
$tipo_usuario = $input['tipo_usuario'];
if (!in_array($tipo_usuario, ['caixa', 'sanger'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de usuário inválido.']);
    exit;
}

try {
    // 1. Validar o código de acesso e pegar o ID da unidade
    $stmt = $conn->prepare("SELECT id FROM unidades WHERE codigo_acesso = ?");
    $stmt->bind_param("s", $input['codigo_acesso']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Código de acesso inválido.");
    }
    $unidade = $result->fetch_assoc();
    $id_unidade = $unidade['id'];

    // 2. Verificar se o usuário (CPF) já existe. Se não, criá-lo.
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $input['cpf']);
    $stmt->execute();
    $result = $stmt->get_result();
    $id_usuario = null;

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $id_usuario = $usuario['id'];
        // Atualiza tipo_usuario se necessário
        $stmt_update_tipo = $conn->prepare("UPDATE usuarios SET tipo_usuario = ? WHERE id = ?");
        $stmt_update_tipo->bind_param("si", $tipo_usuario, $id_usuario);
        $stmt_update_tipo->execute();
    } else {
        // Se não existe, insere o novo usuário já com tipo_usuario
        $stmt_insert_user = $conn->prepare("INSERT INTO usuarios (nome, cpf, tipo_usuario) VALUES (?, ?, ?)");
        $stmt_insert_user->bind_param("sss", $input['nome_completo'], $input['cpf'], $tipo_usuario);
        $stmt_insert_user->execute();
        $id_usuario = $conn->insert_id;
    }
    
    // 3. Verificar se já não existe uma associação para este usuário nesta unidade
    $stmt_check_assoc = $conn->prepare("SELECT id FROM associacoes_usuario_unidade WHERE id_usuario = ? AND id_unidade = ?");
    $stmt_check_assoc->bind_param("ii", $id_usuario, $id_unidade);
    $stmt_check_assoc->execute();
    $assoc_result = $stmt_check_assoc->get_result();
    if ($assoc_result->num_rows > 0) {
        throw new Exception("Você já possui um cadastro nesta unidade.");
    }

    // 4. Criar a nova associação com status 'Pendente'
    $senha_hash = password_hash($input['senha'], PASSWORD_DEFAULT);
    $perfil = 'Caixa'; // Perfil padrão para novos funcionários
    $status = 'Pendente';
    $stmt_insert_assoc = $conn->prepare("INSERT INTO associacoes_usuario_unidade (id_usuario, id_unidade, senha_hash, perfil, status_aprovacao) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_assoc->bind_param("iisss", $id_usuario, $id_unidade, $senha_hash, $perfil, $status);
    $stmt_insert_assoc->execute();

    echo json_encode(['success' => true, 'message' => 'Cadastro enviado com sucesso! Aguarde a aprovação do gestor da unidade.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>