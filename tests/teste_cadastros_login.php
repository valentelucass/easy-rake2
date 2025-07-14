<?php
/**
 * Teste de Cadastros e Login - Easy Rake 2.0
 * Testa o fluxo completo de criação de unidade, funcionário e login
 */

require_once __DIR__ . '/../src/api/db_connect.php';
require_once __DIR__ . '/../src/api/utils/response.php';

echo "=== TESTE DE CADASTROS E LOGIN ===\n\n";

$conn = getConnection();
if (!$conn) {
    echo "❌ Erro: Não foi possível conectar ao banco de dados\n";
    exit;
}

// Limpar dados de teste anteriores
echo "🧹 Limpando dados de teste anteriores...\n";
$conn->query("DELETE FROM aprovacoes_acesso WHERE funcionario_id IN (SELECT id FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf LIKE '%TESTE%'))");
$conn->query("DELETE FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf LIKE '%TESTE%')");
$conn->query("DELETE FROM usuarios WHERE cpf LIKE '%TESTE%'");
$conn->query("DELETE FROM unidades WHERE nome LIKE '%TESTE%'");

// Teste 1: Criar primeira unidade
echo "\n📋 Teste 1: Criar primeira unidade\n";
$dados_unidade = [
    'nome' => 'Poker Base TESTE',
    'telefone' => '84994187843',
    'endereco' => 'Rua Teste, 123 - Centro - Natal/RN',
    'cpf_gestor' => '12924554466',
    'nome_gestor' => 'Lucas Andrade TESTE',
    'email_gestor' => 'teste@teste.com',
    'senha' => '123456',
    'confirmar_senha' => '123456'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/easy-rake/src/api/unidades/criar.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_unidade));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code === 201 && isset($result['success']) && $result['success']) {
    echo "✅ Unidade criada com sucesso\n";
    echo "   Código de acesso: " . $result['data']['codigo_acesso'] . "\n";
    $codigo_acesso = $result['data']['codigo_acesso'];
} else {
    echo "❌ Erro ao criar unidade: " . ($result['message'] ?? 'Erro desconhecido') . "\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Response: $response\n";
    exit;
}

// Teste 2: Criar funcionário
echo "\n👤 Teste 2: Criar funcionário\n";
$dados_funcionario = [
    'codigo_acesso' => $codigo_acesso,
    'nome_completo' => 'João Silva TESTE',
    'tipo_usuario' => 'caixa',
    'cpf' => '11122233344',
    'senha' => '123456',
    'confirmar_senha' => '123456'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/easy-rake/src/api/funcionarios/criar.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_funcionario));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code === 201 && isset($result['success']) && $result['success']) {
    echo "✅ Funcionário criado com sucesso\n";
    echo "   Status: " . $result['data']['funcionario']['status'] . "\n";
} else {
    echo "❌ Erro ao criar funcionário: " . ($result['message'] ?? 'Erro desconhecido') . "\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Response: $response\n";
}

// Teste 3: Login do gestor
echo "\n🔐 Teste 3: Login do gestor\n";
$dados_login = [
    'cpf' => '12924554466',
    'senha' => '123456'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/easy-rake/src/api/auth/login.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_login));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code === 200 && isset($result['success']) && $result['success']) {
    echo "✅ Login do gestor realizado com sucesso\n";
    echo "   Perfil: " . $result['user']['perfil'] . "\n";
    echo "   Unidade: " . $result['user']['unidade'] . "\n";
} else {
    echo "❌ Erro no login do gestor: " . ($result['message'] ?? 'Erro desconhecido') . "\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Response: $response\n";
}

// === POPULAR FUNCIONÁRIOS E SOLICITAÇÕES DE ACESSO ===
function popularFuncionariosComSolicitacoes($conn, $unidade_id, $quantidade = 5) {
    for ($i = 1; $i <= $quantidade; $i++) {
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
        // Cria funcionário (Caixa)
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
}

if (isset($unidade_id)) {
    popularFuncionariosComSolicitacoes($conn, $unidade_id, 8);
}

// Teste 4: Verificar estrutura do banco
echo "\n🗄️ Teste 4: Verificar estrutura do banco\n";

// Verificar se as tabelas existem
$tabelas = ['usuarios', 'unidades', 'funcionarios', 'aprovacoes_acesso'];
foreach ($tabelas as $tabela) {
    $result = $conn->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows > 0) {
        echo "✅ Tabela $tabela existe\n";
    } else {
        echo "❌ Tabela $tabela não existe\n";
    }
}

// Verificar dados inseridos
echo "\n📊 Dados inseridos:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE cpf LIKE '%TESTE%'");
$row = $result->fetch_assoc();
echo "   Usuários de teste: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM unidades WHERE nome LIKE '%TESTE%'");
$row = $result->fetch_assoc();
echo "   Unidades de teste: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM funcionarios f JOIN usuarios u ON f.usuario_id = u.id WHERE u.cpf LIKE '%TESTE%'");
$row = $result->fetch_assoc();
echo "   Funcionários de teste: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM aprovacoes_acesso a JOIN funcionarios f ON a.funcionario_id = f.id JOIN usuarios u ON f.usuario_id = u.id WHERE u.cpf LIKE '%TESTE%'");
$row = $result->fetch_assoc();
echo "   Aprovações de teste: " . $row['total'] . "\n";

echo "\n=== FIM DOS TESTES ===\n";

$conn->close();
?> 