<?php
require_once '../utils/response.php';
header('Content-Type: application/json');
require_once '../db_connect.php';

// Pega os dados JSON enviados pelo JavaScript
$input = json_decode(file_get_contents('php://input'), true);

// Padronizar CPF do gestor (apenas números)
if (isset($input['cpf_gestor'])) {
    $input['cpf_gestor'] = preg_replace('/\D/', '', $input['cpf_gestor']);
}

// Validação básica de campos obrigatórios
$required_fields = ['nome', 'cpf_gestor', 'telefone', 'nome_gestor', 'email_gestor', 'senha', 'confirmar_senha'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        send_json_response(false, "O campo '$field' é obrigatório.", [], null, 400);
    }
}

// Validações específicas
if ($input['senha'] !== $input['confirmar_senha']) {
    send_json_response(false, 'As senhas não coincidem.', [], null, 400);
}

if (!filter_var($input['email_gestor'], FILTER_VALIDATE_EMAIL)) {
    send_json_response(false, 'O formato do e-mail é inválido.', [], null, 400);
}

// Função para gerar código de acesso único
function gerarCodigoAcesso() {
    global $conn;
    do {
        $codigo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        $stmt = $conn->prepare("SELECT id FROM unidades WHERE codigo_acesso = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return $codigo;
        }
    } while (true);
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? OR cpf = ?");
    $stmt->bind_param("ss", $input['email_gestor'], $input['cpf_gestor']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Um usuário com este e-mail ou CPF já está cadastrado no sistema.");
    }

    $codigo_acesso = gerarCodigoAcesso();
    $stmt_insert_unidade = $conn->prepare("INSERT INTO unidades (nome, telefone, codigo_acesso) VALUES (?, ?, ?)");
    $stmt_insert_unidade->bind_param("sss", $input['nome'], $input['telefone'], $codigo_acesso);
    $stmt_insert_unidade->execute();
    $id_unidade = $conn->insert_id;

    $tipo_usuario = 'gestor';
    $senha_hash = password_hash($input['senha'], PASSWORD_DEFAULT);
    $stmt_insert_user = $conn->prepare("INSERT INTO usuarios (nome, email, cpf, senha, perfil, status, tipo_usuario) VALUES (?, ?, ?, ?, 'Gestor', 'Ativo', ?)");
    $stmt_insert_user->bind_param("sssss", $input['nome_gestor'], $input['email_gestor'], $input['cpf_gestor'], $senha_hash, $tipo_usuario);
    $stmt_insert_user->execute();
    $id_usuario = $conn->insert_id;

    $stmt_insert_assoc = $conn->prepare("INSERT INTO associacoes_usuario_unidade (id_usuario, id_unidade, senha_hash, perfil, status_aprovacao) VALUES (?, ?, ?, 'Gestor', 'Aprovado')");
    $stmt_insert_assoc->bind_param("iis", $id_usuario, $id_unidade, $senha_hash);
    $stmt_insert_assoc->execute();

    $conn->commit();

    send_json_response(true, 'Unidade criada com sucesso! O gestor pode fazer login com suas credenciais.', [
        'unidade_id' => $id_unidade,
        'gestor_email' => $input['email_gestor'],
        'codigo_acesso' => $codigo_acesso
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao registrar unidade: " . $e->getMessage());
    send_json_response(false, 'Erro ao registrar unidade.', [], $e->getMessage(), 500);
}

$conn->close();
?>