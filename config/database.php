<?php
/**
 * Configuração de conexão com o banco de dados para XAMPP
 */

// Configurações do banco de dados para XAMPP
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '36140888');
define('DB_NAME', 'easy_rake');
define('DB_PORT', 3307);

/**
 * Cria o banco de dados se não existir
 * @return bool True se criado com sucesso ou já existe
 */
function createDatabaseIfNotExists() {
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);
        
        if ($conn->connect_error) {
            error_log("Erro na conexão com o MySQL: " . $conn->connect_error);
            return false;
        }
        
        // Criar banco se não existir
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if (!$conn->query($sql)) {
            error_log("Erro ao criar banco de dados: " . $conn->error);
            $conn->close();
            return false;
        }
        
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        error_log("Exceção ao criar banco: " . $e->getMessage());
        return false;
    }
}

/**
 * Executa o script SQL de criação das tabelas
 * @return bool True se executado com sucesso
 */
function setupDatabase() {
    // Incluir o arquivo de conexão principal
    require_once __DIR__ . '/../src/api/db_connect.php';
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        // Ler o arquivo SQL
        $sql_file = __DIR__ . '/database_setup.sql';
        
        if (!file_exists($sql_file)) {
            error_log("Arquivo SQL não encontrado: " . $sql_file);
            return false;
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Executar as queries
        if (!$conn->multi_query($sql_content)) {
            error_log("Erro ao executar script SQL: " . $conn->error);
            return false;
        }
        
        // Consumir todos os resultados
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        error_log("Exceção ao configurar banco: " . $e->getMessage());
        $conn->close();
        return false;
    }
}
?> 