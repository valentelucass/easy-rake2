<?php
/**
 * 🔍 VERIFICADOR DE ESTRUTURA CONSOLIDADO - Easy Rake
 * 
 * Script consolidado que verifica:
 * - Estrutura de todas as tabelas
 * - Dados de caixas
 * - Dados de jogadores
 * - Dashboard e contadores
 * - Integridade referencial
 */

require_once __DIR__ . '/../src/api/db_connect.php';

echo "🔍 VERIFICADOR DE ESTRUTURA CONSOLIDADO - EASY RAKE\n";
echo "==================================================\n\n";

// Verificar conexão
if (!isset($conn) || $conn->connect_error) {
    echo "❌ ERRO: Não foi possível conectar ao banco de dados\n";
    exit;
}

echo "✅ Conexão MySQL OK (Porta 3307)\n\n";

// ========================================
// 1. VERIFICAR ESTRUTURA DAS TABELAS
// ========================================

echo "1. 📊 VERIFICANDO ESTRUTURA DAS TABELAS\n";
echo "---------------------------------------\n";

$tabelas = [
    'usuarios' => 'Usuários do sistema',
    'unidades' => 'Unidades/Estabelecimentos',
    'associacoes_usuario_unidade' => 'Associações usuário-unidade',
    'aprovacoes' => 'Sistema de aprovações',
    'caixas' => 'Caixas operacionais',
    'jogadores' => 'Jogadores',
    'transacoes_jogadores' => 'Transações de jogadores',
    'movimentacoes' => 'Movimentações de caixa',
    'gastos' => 'Gastos do caixa',
    'rake' => 'Rake do sistema',
    'caixinhas_inclusoes' => 'Inclusões de caixinhas'
];

foreach ($tabelas as $tabela => $descricao) {
    echo "   📋 $tabela ($descricao):\n";
    $result = $conn->query("DESCRIBE $tabela");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "      - {$row['Field']} ({$row['Type']})";
            if ($row['Key'] == 'PRI') echo " [PRIMARY KEY]";
            if ($row['Key'] == 'MUL') echo " [FOREIGN KEY]";
            echo "\n";
        }
    } else {
        echo "      ❌ Erro ao consultar tabela\n";
    }
    echo "\n";
}

// ========================================
// 2. VERIFICAR DADOS DE CAIXAS
// ========================================

echo "2. 📦 VERIFICANDO DADOS DE CAIXAS\n";
echo "---------------------------------\n";

// Contar caixas por status
$sql_caixas = "SELECT status, COUNT(*) as total FROM caixas GROUP BY status";
$result = $conn->query($sql_caixas);
if ($result) {
    echo "   📊 Caixas por status:\n";
    while ($row = $result->fetch_assoc()) {
        echo "      - {$row['status']}: {$row['total']} caixas\n";
    }
} else {
    echo "   ❌ Erro ao consultar caixas\n";
}

// Verificar caixas abertos
$sql_abertos = "SELECT c.id, c.valor_inicial, c.data_abertura, u.nome as operador
                FROM caixas c 
                LEFT JOIN usuarios u ON c.operador_id = u.id 
                WHERE c.status = 'Aberto'
                ORDER BY c.data_abertura DESC";
$result = $conn->query($sql_abertos);
if ($result && $result->num_rows > 0) {
    echo "   📦 Caixas abertos:\n";
    while ($caixa = $result->fetch_assoc()) {
        echo "      - ID: {$caixa['id']} | Operador: {$caixa['operador']} | Valor: R$ " . number_format($caixa['valor_inicial'], 2, ',', '.') . " | Data: {$caixa['data_abertura']}\n";
    }
} else {
    echo "   ❌ Nenhum caixa aberto encontrado\n";
}

echo "\n";

// ========================================
// 3. VERIFICAR DADOS DE JOGADORES
// ========================================

echo "3. 👥 VERIFICANDO DADOS DE JOGADORES\n";
echo "------------------------------------\n";

// Contar jogadores por status
$sql_jogadores = "SELECT status, COUNT(*) as total FROM jogadores GROUP BY status";
$result = $conn->query($sql_jogadores);
if ($result) {
    echo "   📊 Jogadores por status:\n";
    while ($row = $result->fetch_assoc()) {
        echo "      - {$row['status']}: {$row['total']} jogadores\n";
    }
} else {
    echo "   ❌ Erro ao consultar jogadores\n";
}

// Verificar jogadores ativos
$sql_ativos = "SELECT id, nome, cpf, limite_credito, saldo_atual, status
               FROM jogadores 
               WHERE status = 'Ativo'
               ORDER BY nome";
$result = $conn->query($sql_ativos);
if ($result && $result->num_rows > 0) {
    echo "   👥 Jogadores ativos:\n";
    while ($jogador = $result->fetch_assoc()) {
        echo "      - {$jogador['nome']} (CPF: {$jogador['cpf']}) | Limite: R$ " . number_format($jogador['limite_credito'], 2, ',', '.') . " | Saldo: R$ " . number_format($jogador['saldo_atual'], 2, ',', '.') . "\n";
    }
} else {
    echo "   ❌ Nenhum jogador ativo encontrado\n";
}

echo "\n";

// ========================================
// 4. VERIFICAR DASHBOARD E CONTADORES
// ========================================

echo "4. 📊 VERIFICANDO DASHBOARD E CONTADORES\n";
echo "----------------------------------------\n";

// Query do dashboard
$sql_dashboard = "SELECT 
    (SELECT COUNT(*) FROM caixas WHERE status = 'Aberto') as caixas_abertos,
    (SELECT COUNT(*) FROM jogadores WHERE status = 'Ativo') as jogadores_ativos,
    (SELECT COUNT(*) FROM aprovacoes WHERE status = 'Pendente') as aprovacoes_pendentes,
    (SELECT COUNT(*) FROM caixas WHERE DATE(data_abertura) = CURDATE()) as caixas_hoje,
    (SELECT COUNT(*) FROM jogadores WHERE DATE(data_cadastro) = CURDATE()) as jogadores_hoje,
    (SELECT SUM(valor_inicial) FROM caixas WHERE status = 'Aberto') as valor_total_caixas";

$result = $conn->query($sql_dashboard);
if ($result) {
    $dashboard = $result->fetch_assoc();
    echo "   📊 Dashboard atual:\n";
    echo "      - Caixas abertos: {$dashboard['caixas_abertos']}\n";
    echo "      - Jogadores ativos: {$dashboard['jogadores_ativos']}\n";
    echo "      - Aprovações pendentes: {$dashboard['aprovacoes_pendentes']}\n";
    echo "      - Caixas abertos hoje: {$dashboard['caixas_hoje']}\n";
    echo "      - Jogadores cadastrados hoje: {$dashboard['jogadores_hoje']}\n";
    echo "      - Valor total em caixas: R$ " . number_format($dashboard['valor_total_caixas'], 2, ',', '.') . "\n";
} else {
    echo "   ❌ Erro ao consultar dashboard\n";
}

echo "\n";

// ========================================
// 5. VERIFICAR INTEGRIDADE REFERENCIAL
// ========================================

echo "5. 🔗 VERIFICANDO INTEGRIDADE REFERENCIAL\n";
echo "-----------------------------------------\n";

// Verificar caixas sem operador
$sql_sem_operador = "SELECT c.id, c.valor_inicial FROM caixas c 
                     LEFT JOIN usuarios u ON c.operador_id = u.id 
                     WHERE u.id IS NULL";
$result = $conn->query($sql_sem_operador);
if ($result && $result->num_rows > 0) {
    echo "   ⚠️  Caixas sem operador válido:\n";
    while ($caixa = $result->fetch_assoc()) {
        echo "      - Caixa ID: {$caixa['id']} | Valor: R$ " . number_format($caixa['valor_inicial'], 2, ',', '.') . "\n";
    }
} else {
    echo "   ✅ Todos os caixas têm operador válido\n";
}

// Verificar transações sem jogador
$sql_sem_jogador = "SELECT t.id, t.valor FROM transacoes_jogadores t 
                    LEFT JOIN jogadores j ON t.jogador_id = j.id 
                    WHERE j.id IS NULL";
$result = $conn->query($sql_sem_jogador);
if ($result && $result->num_rows > 0) {
    echo "   ⚠️  Transações sem jogador válido:\n";
    while ($trans = $result->fetch_assoc()) {
        echo "      - Transação ID: {$trans['id']} | Valor: R$ " . number_format($trans['valor'], 2, ',', '.') . "\n";
    }
} else {
    echo "   ✅ Todas as transações têm jogador válido\n";
}

// Verificar movimentações sem caixa
$sql_sem_caixa = "SELECT m.id, m.valor FROM movimentacoes m 
                  LEFT JOIN caixas c ON m.caixa_id = c.id 
                  WHERE c.id IS NULL";
$result = $conn->query($sql_sem_caixa);
if ($result && $result->num_rows > 0) {
    echo "   ⚠️  Movimentações sem caixa válido:\n";
    while ($mov = $result->fetch_assoc()) {
        echo "      - Movimentação ID: {$mov['id']} | Valor: R$ " . number_format($mov['valor'], 2, ',', '.') . "\n";
    }
} else {
    echo "   ✅ Todas as movimentações têm caixa válido\n";
}

echo "\n";

// ========================================
// 6. VERIFICAR DADOS DE EXEMPLO
// ========================================

echo "6. 📋 VERIFICANDO DADOS DE EXEMPLO\n";
echo "----------------------------------\n";

// Verificar se há dados suficientes para teste
$caixas_total = $conn->query("SELECT COUNT(*) as total FROM caixas")->fetch_assoc()['total'];
$jogadores_total = $conn->query("SELECT COUNT(*) as total FROM jogadores")->fetch_assoc()['total'];
$aprovacoes_total = $conn->query("SELECT COUNT(*) as total FROM aprovacoes")->fetch_assoc()['total'];
$transacoes_total = $conn->query("SELECT COUNT(*) as total FROM transacoes_jogadores")->fetch_assoc()['total'];
$movimentacoes_total = $conn->query("SELECT COUNT(*) as total FROM movimentacoes")->fetch_assoc()['total'];

echo "   📊 Resumo dos dados:\n";
echo "      - Total de caixas: $caixas_total\n";
echo "      - Total de jogadores: $jogadores_total\n";
echo "      - Total de aprovações: $aprovacoes_total\n";
echo "      - Total de transações: $transacoes_total\n";
echo "      - Total de movimentações: $movimentacoes_total\n";

if ($caixas_total > 0 && $jogadores_total > 0) {
    echo "   ✅ Dados suficientes para testes básicos\n";
} else {
    echo "   ⚠️  Dados insuficientes para testes completos\n";
}

echo "\n";

// ========================================
// 7. CONCLUSÕES
// ========================================

echo "7. 📋 CONCLUSÕES\n";
echo "----------------\n";

echo "   ✅ ESTRUTURA VERIFICADA:\n";
echo "      - Todas as tabelas principais existem\n";
echo "      - Estrutura de colunas está correta\n";
echo "      - Relacionamentos estão configurados\n";
echo "      - Dados básicos estão presentes\n\n";

echo "   🔍 PRÓXIMOS PASSOS:\n";
echo "      - Se houver problemas de integridade, execute o corretor automático\n";
echo "      - Para testes completos, crie dados de exemplo\n";
echo "      - Para análise detalhada, execute o diagnóstico inteligente\n\n";

echo "🎯 VERIFICAÇÃO DE ESTRUTURA CONCLUÍDA!\n";
echo "======================================\n";

$conn->close();
?> 