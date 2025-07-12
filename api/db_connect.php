<?php
// Definições do Banco de Dados (usa variáveis de ambiente se disponíveis)
define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '36140888');
define('DB_NAME', getenv('DB_NAME') ?: 'easy_rake');
define('DB_PORT', getenv('DB_PORT') ?: 3307);

// Criar a conexão mysqli
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Checar a conexão
if ($conn->connect_error) {
    error_log('Conexão falhou: ' . $conn->connect_error);
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno ao conectar ao banco de dados.']);
    exit;
}

// Define o charset para UTF-8 para evitar problemas com acentos
$conn->set_charset('utf8mb4');