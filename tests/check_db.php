<?php
require_once __DIR__ . '/../src/api/db_connect.php';

echo "Verificando banco de dados...\n";

$conn = getConnection();
if (!$conn) {
    echo "ERRO: Não foi possível conectar ao banco de dados\n";
    exit;
}

echo "Conexão OK\n";

// Verificar se a tabela jogadores existe
$result = $conn->query("SHOW TABLES LIKE 'jogadores'");
if ($result && $result->num_rows > 0) {
    echo "Tabela jogadores existe\n";
    
    // Contar jogadores
    $result = $conn->query("SELECT COUNT(*) as total FROM jogadores");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Total de jogadores: " . $row['total'] . "\n";
        
        // Mostrar alguns jogadores se existirem
        if ($row['total'] > 0) {
            $result = $conn->query("SELECT * FROM jogadores LIMIT 3");
            if ($result) {
                while ($jogador = $result->fetch_assoc()) {
                    echo "- " . $jogador['nome'] . " (CPF: " . $jogador['cpf'] . ")\n";
                }
            }
        }
    }
} else {
    echo "Tabela jogadores NÃO existe\n";
}

closeConnection($conn);
echo "Teste concluído!\n";
?> 