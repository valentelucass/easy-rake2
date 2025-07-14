<?php
require_once __DIR__ . '/../src/api/db_connect.php';
$conn = getConnection();

// Limpar funcionários e usuários de teste
for ($i = 1; $i <= 20; $i++) {
    $cpf = sprintf('900000000%02d', $i);
    $conn->query("DELETE FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf = '$cpf')");
    $conn->query("DELETE FROM usuarios WHERE cpf = '$cpf'");
}

// Buscar unidade de teste
$res = $conn->query('SELECT id FROM unidades ORDER BY id LIMIT 1');
$unidade = $res ? $res->fetch_assoc() : null;
if (!$unidade) {
    echo "Nenhuma unidade encontrada.\n";
    exit(1);
}
$unidade_id = $unidade['id'];

// Popular funcionários e solicitações
for ($i = 1; $i <= 8; $i++) {
    $nome = "Caixa Teste $i";
    $cpf = sprintf('900000000%02d', $i);
    $email = "caixa$i@teste.com";
    $senha = password_hash('123456', PASSWORD_DEFAULT);
    // Cria usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, cpf, email, senha, status, data_criacao) VALUES (?, ?, ?, ?, 'Ativo', NOW())");
    $stmt->bind_param('ssss', $nome, $cpf, $email, $senha);
    $stmt->execute();
    $usuario_id = $conn->insert_id;
    $stmt->close();
    // Cria funcionário (Caixa ou Sanger)
    $cargo = ($i % 2 == 0) ? 'Sanger' : 'Caixa';
    $stmt = $conn->prepare("INSERT INTO funcionarios (usuario_id, unidade_id, cargo, status, data_vinculo) VALUES (?, ?, ?, 'Pendente', NOW())");
    $stmt->bind_param('iis', $usuario_id, $unidade_id, $cargo);
    $stmt->execute();
    $funcionario_id = $conn->insert_id;
    $stmt->close();
    // Cria solicitação de aprovação de acesso
    $tipo = $cargo;
    $stmt = $conn->prepare("INSERT INTO aprovacoes_acesso (funcionario_id, tipo, status, data_solicitacao) VALUES (?, ?, 'Pendente', NOW())");
    $stmt->bind_param('is', $funcionario_id, $tipo);
    $stmt->execute();
    $stmt->close();
}

echo "Funcionários e solicitações de acesso populados com sucesso.\n";
$conn->close(); 