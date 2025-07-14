<?php
/**
 * Teste para verificar funções duplicadas no sistema
 * Identifica possíveis conflitos de redeclaração de funções
 */

echo "=== VERIFICAÇÃO DE FUNÇÕES DUPLICADAS ===\n\n";

// Array para armazenar todas as funções encontradas
$funcoes_encontradas = [];
$arquivos_verificados = [];

// Função para escanear diretório recursivamente
function scanDirectory($dir, &$funcoes_encontradas, &$arquivos_verificados) {
    $files = glob($dir . '/*.php');
    
    foreach ($files as $file) {
        $arquivos_verificados[] = $file;
        $content = file_get_contents($file);
        
        // Buscar declarações de funções
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $function_name) {
                if (!isset($funcoes_encontradas[$function_name])) {
                    $funcoes_encontradas[$function_name] = [];
                }
                $funcoes_encontradas[$function_name][] = $file;
            }
        }
    }
    
    // Verificar subdiretórios
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        scanDirectory($subdir, $funcoes_encontradas, $arquivos_verificados);
    }
}

// Escanear diretórios principais
$diretorios = [
    __DIR__ . '/../src',
    __DIR__ . '/../config',
    __DIR__ . '/../public'
];

foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        scanDirectory($dir, $funcoes_encontradas, $arquivos_verificados);
    }
}

echo "Arquivos PHP verificados: " . count($arquivos_verificados) . "\n\n";

// Verificar funções duplicadas
$funcoes_duplicadas = [];
foreach ($funcoes_encontradas as $function_name => $arquivos) {
    if (count($arquivos) > 1) {
        $funcoes_duplicadas[$function_name] = $arquivos;
    }
}

if (empty($funcoes_duplicadas)) {
    echo "✅ Nenhuma função duplicada encontrada!\n";
} else {
    echo "❌ FUNÇÕES DUPLICADAS ENCONTRADAS:\n\n";
    
    foreach ($funcoes_duplicadas as $function_name => $arquivos) {
        echo "🔴 Função: {$function_name}\n";
        echo "   Arquivos:\n";
        foreach ($arquivos as $arquivo) {
            $relative_path = str_replace(__DIR__ . '/../', '', $arquivo);
            echo "   - {$relative_path}\n";
        }
        echo "\n";
    }
}

// Verificar includes problemáticos
echo "=== VERIFICAÇÃO DE INCLUDES ===\n\n";

$includes_problematicos = [];
foreach ($arquivos_verificados as $arquivo) {
    $content = file_get_contents($arquivo);
    
    // Buscar includes/requires
    preg_match_all('/(?:require|include)(?:_once)?\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $include) {
            // Verificar se o include pode causar conflito
            if (strpos($include, 'database.php') !== false || strpos($include, 'db_connect.php') !== false) {
                $relative_path = str_replace(__DIR__ . '/../', '', $arquivo);
                $includes_problematicos[] = [
                    'arquivo' => $relative_path,
                    'include' => $include
                ];
            }
        }
    }
}

if (!empty($includes_problematicos)) {
    echo "⚠️  INCLUDES DE CONEXÃO ENCONTRADOS:\n\n";
    foreach ($includes_problematicos as $item) {
        echo "📁 {$item['arquivo']}\n";
        echo "   Include: {$item['include']}\n\n";
    }
}

// Teste de carregamento de arquivos principais
echo "=== TESTE DE CARREGAMENTO ===\n\n";

$arquivos_principais = [
    'src/api/db_connect.php',
    'src/api/utils/auth.php',
    'src/api/utils/helpers.php',
    'config/database.php'
];

foreach ($arquivos_principais as $arquivo) {
    $caminho_completo = __DIR__ . '/../' . $arquivo;
    
    if (file_exists($caminho_completo)) {
        try {
            // Tentar incluir o arquivo
            ob_start();
            include_once $caminho_completo;
            ob_end_clean();
            echo "✅ {$arquivo} - Carregado com sucesso\n";
        } catch (Exception $e) {
            echo "❌ {$arquivo} - Erro: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️  {$arquivo} - Arquivo não encontrado\n";
    }
}

// Teste de funções específicas
echo "\n=== TESTE DE FUNÇÕES ESPECÍFICAS ===\n\n";

$funcoes_teste = [
    'getConnection',
    'canManageUsers',
    'getCurrentFuncionarioId',
    'executePreparedQuery'
];

foreach ($funcoes_teste as $funcao) {
    if (function_exists($funcao)) {
        echo "✅ {$funcao}() - Disponível\n";
    } else {
        echo "❌ {$funcao}() - Não encontrada\n";
    }
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?> 