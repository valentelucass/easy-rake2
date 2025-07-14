<?php
/**
 * Teste completo do sistema Easy Rake
 * Verifica se todas as funcionalidades principais estão funcionando
 */

echo "=== TESTE COMPLETO DO SISTEMA EASY RAKE ===\n\n";

// Incluir arquivos principais
require_once __DIR__ . '/../src/api/db_connect.php';
require_once __DIR__ . '/../src/api/utils/auth.php';
require_once __DIR__ . '/../src/api/utils/helpers.php';

// Teste 1: Conexão com o banco
echo "1. TESTE DE CONEXÃO COM BANCO:\n";
try {
    $conn = getConnection();
    if ($conn) {
        echo "✅ Conexão com banco estabelecida com sucesso\n";
        closeConnection($conn);
    } else {
        echo "❌ Falha na conexão com banco\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    exit(1);
}

// Teste 2: Verificar funções de autenticação
echo "\n2. TESTE DE FUNÇÕES DE AUTENTICAÇÃO:\n";
$funcoes_auth = [
    'isAuthenticated',
    'hasPermission',
    'canManageUnits',
    'canManageUsers',
    'canApprove',
    'getCurrentFuncionarioId',
    'getCurrentUnidadeId',
    'isFirstUser'
];

foreach ($funcoes_auth as $funcao) {
    if (function_exists($funcao)) {
        echo "✅ {$funcao}() - Disponível\n";
    } else {
        echo "❌ {$funcao}() - Não encontrada\n";
    }
}

// Teste 3: Verificar funções de banco
echo "\n3. TESTE DE FUNÇÕES DE BANCO:\n";
$funcoes_db = [
    'getConnection',
    'closeConnection',
    'executeQuery',
    'executePreparedQuery',
    'testConnection'
];

foreach ($funcoes_db as $funcao) {
    if (function_exists($funcao)) {
        echo "✅ {$funcao}() - Disponível\n";
    } else {
        echo "❌ {$funcao}() - Não encontrada\n";
    }
}

// Teste 4: Verificar funções auxiliares
echo "\n4. TESTE DE FUNÇÕES AUXILIARES:\n";
$funcoes_helpers = [
    'getCurrentFuncionario'
];

foreach ($funcoes_helpers as $funcao) {
    if (function_exists($funcao)) {
        echo "✅ {$funcao}() - Disponível\n";
    } else {
        echo "❌ {$funcao}() - Não encontrada\n";
    }
}

// Teste 5: Verificar estrutura do banco
echo "\n5. TESTE DE ESTRUTURA DO BANCO:\n";
try {
    $conn = getConnection();
    
    $tabelas_principais = [
        'usuarios',
        'unidades',
        'funcionarios',
        'jogadores',
        'caixas',
        'caixinhas',
        'gastos',
        'rake',
        'aprovacoes',
        'aprovacoes_acesso'
    ];
    
    foreach ($tabelas_principais as $tabela) {
        $result = $conn->query("SHOW TABLES LIKE '{$tabela}'");
        
        if ($result && $result->num_rows > 0) {
            echo "✅ Tabela '{$tabela}' - Existe\n";
        } else {
            echo "❌ Tabela '{$tabela}' - Não encontrada\n";
        }
    }
    
    closeConnection($conn);
} catch (Exception $e) {
    echo "❌ Erro ao verificar estrutura do banco: " . $e->getMessage() . "\n";
}

// Teste 6: Verificar endpoints principais
echo "\n6. TESTE DE ENDPOINTS PRINCIPAIS:\n";
$endpoints = [
    '../src/api/auth/login.php',
    '../src/api/auth/logout.php',
    '../src/api/jogadores/listar.php',
    '../src/api/caixas/listar.php',
    '../src/api/dashboard/get_dashboard.php'
];

foreach ($endpoints as $endpoint) {
    $caminho_completo = __DIR__ . '/' . $endpoint;
    if (file_exists($caminho_completo)) {
        echo "✅ {$endpoint} - Existe\n";
    } else {
        echo "❌ {$endpoint} - Não encontrado\n";
    }
}

// Teste 7: Verificar arquivos de frontend
echo "\n7. TESTE DE ARQUIVOS DE FRONTEND:\n";
$arquivos_frontend = [
    '../public/index.php',
    '../public/abrir-caixa.php',
    '../public/jogadores.php',
    '../public/aprovacoes.php',
    '../public/js/features/auth.js',
    '../public/js/features/dashboard.js',
    '../public/css/main.css'
];

foreach ($arquivos_frontend as $arquivo) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho_completo)) {
        echo "✅ {$arquivo} - Existe\n";
    } else {
        echo "❌ {$arquivo} - Não encontrado\n";
    }
}

// Teste 8: Verificar includes problemáticos
echo "\n8. TESTE DE INCLUDES PROBLEMÁTICOS:\n";
$includes_problematicos = [
    '../src/api/aprovacoes/acao.php',
    '../src/api/aprovacoes/listar_pendentes.php',
    '../src/api/aprovacoes/listar_historico.php'
];

foreach ($includes_problematicos as $arquivo) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho_completo)) {
        $conteudo = file_get_contents($caminho_completo);
        if (strpos($conteudo, '../../../../api/db_connect.php') !== false) {
            echo "⚠️  {$arquivo} - Tem include problemático (caminho relativo incorreto)\n";
        } else {
            echo "✅ {$arquivo} - Include correto\n";
        }
    } else {
        echo "❌ {$arquivo} - Não encontrado\n";
    }
}

// Teste 9: Verificar se não há erros de sintaxe
echo "\n9. TESTE DE SINTAXE PHP:\n";
$arquivos_php_principais = [
    '../src/api/db_connect.php',
    '../src/api/utils/auth.php',
    '../src/api/utils/helpers.php',
    '../config/database.php'
];

foreach ($arquivos_php_principais as $arquivo) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho_completo)) {
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($caminho_completo) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "✅ {$arquivo} - Sintaxe OK\n";
        } else {
            echo "❌ {$arquivo} - Erro de sintaxe: " . implode("\n", $output) . "\n";
        }
    } else {
        echo "❌ {$arquivo} - Não encontrado\n";
    }
}

echo "\n=== FIM DO TESTE COMPLETO ===\n";
echo "Se todos os testes passaram, o sistema está funcionando corretamente!\n";
?> 