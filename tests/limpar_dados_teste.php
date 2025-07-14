<?php
/**
 * Script para limpar dados de teste do banco
 */

require_once __DIR__ . '/../src/api/db_connect.php';
$conn = getConnection();

if (!$conn) {
    echo "❌ Erro: Não foi possível conectar ao banco de dados\n";
    exit;
}

echo "🧹 Limpando dados de teste...\n";

// Limpar dados de teste (usando LIKE para pegar variações)
$conn->query("DELETE FROM aprovacoes_acesso WHERE funcionario_id IN (SELECT id FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf LIKE '%TESTE%' OR cpf LIKE '%12924554466%'))");
$conn->query("DELETE FROM aprovacoes WHERE funcionario_id IN (SELECT id FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf LIKE '%TESTE%' OR cpf LIKE '%12924554466%'))");
$conn->query("DELETE FROM funcionarios WHERE usuario_id IN (SELECT id FROM usuarios WHERE cpf LIKE '%TESTE%' OR cpf LIKE '%12924554466%')");
$conn->query("DELETE FROM usuarios WHERE cpf LIKE '%TESTE%' OR cpf LIKE '%12924554466%'");
$conn->query("DELETE FROM unidades WHERE nome LIKE '%TESTE%' OR nome LIKE '%Poker Base%'");

echo "✅ Dados de teste removidos com sucesso!\n";

$conn->close();
?> 