<?php
/**
 * Configuração de conexão com o banco de dados
 * Suporta variáveis de ambiente para configuração flexível
 */

// Configurações do banco de dados
define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '36140888');
define('DB_NAME', getenv('DB_NAME') ?: 'easy_rake');
define('DB_PORT', getenv('DB_PORT') ?: 3307);

/**
 * Estabelece conexão com o banco de dados MySQL
 * @return mysqli|false Retorna a conexão ou false em caso de erro
 */
function getConnection() {
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            error_log("Erro na conexão com o banco: " . $conn->connect_error);
            return false;
        }
        
        // Configurar charset para UTF-8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Exceção na conexão com o banco: " . $e->getMessage());
        return false;
    }
}

/**
 * Fecha a conexão com o banco de dados
 * @param mysqli $conn Conexão a ser fechada
 */
function closeConnection($conn) {
    if ($conn && $conn instanceof mysqli) {
        $conn->close();
    }
}

/**
 * Executa uma query com tratamento de erro
 * @param mysqli $conn Conexão com o banco
 * @param string $sql Query SQL a ser executada
 * @return mysqli_result|bool Resultado da query ou false em caso de erro
 */
function executeQuery($conn, $sql) {
    try {
        $result = $conn->query($sql);
        
        if ($result === false) {
            error_log("Erro na execução da query: " . $conn->error);
            return false;
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Exceção na execução da query: " . $e->getMessage());
        return false;
    }
}

/**
 * Prepara e executa uma query preparada
 * @param mysqli $conn Conexão com o banco
 * @param string $sql Query SQL com placeholders
 * @param string $types Tipos dos parâmetros (i, d, s, b)
 * @param array $params Parâmetros para a query
 * @return mysqli_stmt|false Statement preparado ou false em caso de erro
 */
function executePreparedQuery($conn, $sql, $types, $params) {
    try {
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Erro na preparação da query: " . $conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Erro na execução da query preparada: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        return $stmt;
    } catch (Exception $e) {
        error_log("Exceção na query preparada: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se a conexão com o banco está funcionando
 * @return bool True se a conexão está OK, false caso contrário
 */
function testConnection() {
    $conn = getConnection();
    
    if ($conn === false) {
        return false;
    }
    
    closeConnection($conn);
    return true;
}

// Teste automático da conexão (opcional - pode ser comentado em produção)
// define('TEST_DB_CONNECTION', true); // Descomente para testar
if (defined('TEST_DB_CONNECTION') && constant('TEST_DB_CONNECTION')) {
    if (!testConnection()) {
        error_log("ERRO: Não foi possível conectar ao banco de dados");
    }
}
?> 