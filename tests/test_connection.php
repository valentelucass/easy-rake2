<?php
/**
 * Teste de Conexão com Banco de Dados
 */

echo "=== TESTE DE CONEXÃO ===\n\n";

// Teste 1: Conexão sem senha (padrão XAMPP)
echo "1. Testando conexão sem senha...\n";
try {
    $conn = new mysqli('localhost', 'root', '', '', 3306);
    
    if ($conn->connect_error) {
        echo "❌ Erro: " . $conn->connect_error . "\n";
    } else {
        echo "✅ Conexão MySQL estabelecida\n";
        
        // Criar banco se não existir
        $sql = "CREATE DATABASE IF NOT EXISTS easy_rake DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql)) {
            echo "✅ Banco 'easy_rake' criado/verificado\n";
        } else {
            echo "❌ Erro ao criar banco: " . $conn->error . "\n";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Exceção: " . $e->getMessage() . "\n";
}

// Teste 2: Conexão com o banco específico
echo "\n2. Testando conexão com banco 'easy_rake'...\n";
try {
    $conn = new mysqli('localhost', 'root', '', 'easy_rake', 3306);
    
    if ($conn->connect_error) {
        echo "❌ Erro: " . $conn->connect_error . "\n";
    } else {
        echo "✅ Conexão com banco 'easy_rake' estabelecida\n";
        
        // Verificar se tabelas existem
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            $tabelas = [];
            while ($row = $result->fetch_array()) {
                $tabelas[] = $row[0];
            }
            
            if (empty($tabelas)) {
                echo "ℹ️  Nenhuma tabela encontrada (banco vazio)\n";
            } else {
                echo "📋 Tabelas encontradas:\n";
                foreach ($tabelas as $tabela) {
                    echo "   - $tabela\n";
                }
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Exceção: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?> 