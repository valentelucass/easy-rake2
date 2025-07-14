<?php
/**
 * Script para corrigir includes nos arquivos PHP após reorganização
 */

echo "🔧 CORRIGINDO INCLUDES APÓS REORGANIZAÇÃO\n";
echo "========================================\n\n";

// Mapeamento de correções necessárias
$correcoes = [
    // Testes
    'tests/check_db.php' => [
        'old' => "require_once 'api/db_connect.php';",
        'new' => "require_once __DIR__ . '/../src/api/db_connect.php';"
    ],
    'tests/analise_alinhamento_banco_apis.php' => [
        'old' => "'api_pasta' => 'api/",
        'new' => "'api_pasta' => 'src/api/"
    ],
    'tests/verificador_estrutura.php' => [
        'old' => "require_once 'api/db_connect.php';",
        'new' => "require_once __DIR__ . '/../src/api/db_connect.php';"
    ],
    'tests/corretor_automatico.php' => [
        'old' => "require_once 'api/db_connect.php';",
        'new' => "require_once __DIR__ . '/../src/api/db_connect.php';"
    ],
    'tests/sistema_ia.php' => [
        'old' => "require_once 'api/db_connect.php';",
        'new' => "require_once __DIR__ . '/../src/api/db_connect.php';"
    ]
];

// Aplicar correções
foreach ($correcoes as $arquivo => $correcao) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        $conteudo_original = $conteudo;
        
        $conteudo = str_replace($correcao['old'], $correcao['new'], $conteudo);
        
        if ($conteudo !== $conteudo_original) {
            file_put_contents($arquivo, $conteudo);
            echo "✅ $arquivo - Corrigido\n";
        } else {
            echo "⚠️  $arquivo - Nenhuma correção necessária\n";
        }
    } else {
        echo "❌ $arquivo - Arquivo não encontrado\n";
    }
}

// Corrigir includes nas APIs
echo "\n🔧 CORRIGINDO APIS EM src/api/\n";
echo "==============================\n\n";

$api_files = glob('src/api/**/*.php');
foreach ($api_files as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $conteudo_original = $conteudo;
    
    // Corrigir includes relativos
    $conteudo = str_replace(
        "require_once '../db_connect.php';",
        "require_once __DIR__ . '/../db_connect.php';",
        $conteudo
    );
    
    $conteudo = str_replace(
        "require_once '../utils/",
        "require_once __DIR__ . '/../utils/",
        $conteudo
    );
    
    $conteudo = str_replace(
        "require_once '../../api/db_connect.php';",
        "require_once __DIR__ . '/../../db_connect.php';",
        $conteudo
    );
    
    if ($conteudo !== $conteudo_original) {
        file_put_contents($arquivo, $conteudo);
        echo "✅ $arquivo - Corrigido\n";
    }
}

// Corrigir includes nos arquivos public
echo "\n🔧 CORRIGINDO ARQUIVOS EM public/\n";
echo "==================================\n\n";

$public_files = glob('public/*.php');
foreach ($public_files as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    $conteudo_original = $conteudo;
    
    // Corrigir includes para src/
    $conteudo = str_replace(
        "include 'includes/",
        "include '../src/includes/",
        $conteudo
    );
    
    $conteudo = str_replace(
        "require_once 'includes/",
        "require_once '../src/includes/",
        $conteudo
    );
    
    if ($conteudo !== $conteudo_original) {
        file_put_contents($arquivo, $conteudo);
        echo "✅ $arquivo - Corrigido\n";
    }
}

echo "\n🎉 CORREÇÃO DE INCLUDES CONCLUÍDA!\n";
echo "==================================\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "1. Teste a conexão: php tests/check_db.php\n";
echo "2. Verifique as APIs: php tests/verificador_estrutura.php\n";
echo "3. Execute análise: php tests/analise_alinhamento_banco_apis.php\n";
?> 