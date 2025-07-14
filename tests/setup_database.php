<?php
/**
 * Script de Configuração do Banco de Dados - Easy Rake 2.0
 * Cria o banco e as tabelas automaticamente
 */

require_once __DIR__ . '/../src/api/db_connect.php';

// 1. Criar banco de dados
function criarBancoSeNaoExistir() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);
    if ($conn->connect_error) {
        echo "❌ Erro ao conectar no MySQL: " . $conn->connect_error . "\n";
        exit;
    }
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✅ Banco de dados criado/verificado com sucesso\n";
    } else {
        echo "❌ Erro ao criar banco de dados: " . $conn->error . "\n";
        $conn->close();
        exit;
    }
    $conn->close();
}

// 2. Executar script SQL de criação das tabelas
function executarScriptSQL() {
    $conn = getConnection();
    if (!$conn) {
        echo "❌ Erro ao conectar no banco '" . DB_NAME . "'\n";
        exit;
    }
    $sql_file = __DIR__ . '/../config/database_setup.sql';
    if (!file_exists($sql_file)) {
        echo "❌ Arquivo SQL não encontrado: $sql_file\n";
        $conn->close();
        exit;
    }
    $sql_content = file_get_contents($sql_file);
    if (!$conn->multi_query($sql_content)) {
        echo "❌ Erro ao executar script SQL: " . $conn->error . "\n";
        $conn->close();
        exit;
    }
    // Consumir todos os resultados
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    $conn->close();
    echo "✅ Tabelas criadas com sucesso\n";
}

echo "=== CONFIGURAÇÃO DO BANCO DE DADOS ===\n\n";
echo "1. Criando banco de dados...\n";
criarBancoSeNaoExistir();
echo "\n2. Configurando tabelas...\n";
executarScriptSQL();
echo "\n=== CONFIGURAÇÃO CONCLUÍDA ===\n";
echo "O banco de dados está pronto para uso!\n";
?> 