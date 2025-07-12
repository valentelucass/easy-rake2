<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$caixa_id = isset($_GET['caixa_id']) ? intval($_GET['caixa_id']) : 0;
if ($caixa_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do caixa inválido.']);
    exit;
}

// 1. Valor de Abertura e Inventário Real
$inventario = [
    'valor_abertura' => 0,
    'inventario_real' => null,
];
$res = $conn->query("SELECT valor_inicial, inventario_real FROM caixas WHERE id = $caixa_id LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $inventario['valor_abertura'] = floatval($row['valor_inicial']);
    $inventario['inventario_real'] = $row['inventario_real'] !== null ? floatval($row['inventario_real']) : null;
}

// 2. Fichas Vendidas e Devolvidas (transacoes_jogadores)
$fichas_vendidas = 0;
$fichas_devolvidas = 0;
$res = $conn->query("SELECT tipo, SUM(valor) as total FROM transacoes_jogadores WHERE caixa_id = $caixa_id GROUP BY tipo");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if ($row['tipo'] === 'CREDITO') $fichas_vendidas += floatval($row['total']);
        if ($row['tipo'] === 'DEBITO') $fichas_devolvidas += floatval($row['total']);
    }
}

// 3. Rake (movimentacoes)
$rake_total = 0;
$res = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'Entrada' AND descricao LIKE '%Rake%'");
if ($res && $row = $res->fetch_assoc()) {
    $rake_total = floatval($row['total']);
}

// 4. Caixinhas (bruto, cashback, líquido, por participante)
$caixinhas = [];
$total_bruto_caixinhas = 0;
$total_cashback_caixinhas = 0;
$res = $conn->query("SELECT c.id, c.nome, c.cashback_percent, c.participantes, SUM(i.valor) as total_inclusoes FROM caixinhas c LEFT JOIN caixinhas_inclusoes i ON i.caixinha_id = c.id WHERE i.caixinha_id IS NOT NULL GROUP BY c.id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $bruto = floatval($row['total_inclusoes']);
        $cashback = $row['cashback_percent'] ? $bruto * (floatval($row['cashback_percent'])/100) : 0;
        $liquido = $bruto - $cashback;
        $por_participante = $row['participantes'] > 0 ? $liquido / intval($row['participantes']) : 0;
        $caixinhas[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'bruto' => $bruto,
            'cashback' => $cashback,
            'liquido' => $liquido,
            'por_participante' => $por_participante,
        ];
        $total_bruto_caixinhas += $bruto;
        $total_cashback_caixinhas += $cashback;
    }
}

// 5. Despesas (gastos)
$despesas_totais = 0;
$res = $conn->query("SELECT SUM(valor) as total FROM gastos WHERE caixa_id = $caixa_id");
if ($res && $row = $res->fetch_assoc()) {
    $despesas_totais = floatval($row['total']);
}

// 6. Jogadores Ativos
$jogadores = [];
$res = $conn->query("SELECT j.id, j.nome, SUM(CASE WHEN t.tipo='CREDITO' THEN t.valor ELSE 0 END) as fichas_compradas, SUM(CASE WHEN t.tipo='DEBITO' THEN t.valor ELSE 0 END) as fichas_devolvidas, j.saldo_atual FROM jogadores j LEFT JOIN transacoes_jogadores t ON t.jogador_id = j.id AND t.caixa_id = $caixa_id GROUP BY j.id HAVING fichas_compradas > 0 OR fichas_devolvidas > 0");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $situacao = 'Em dia';
        $saldo = floatval($row['saldo_atual']);
        if ($saldo < 0) $situacao = 'Devedor';
        if ($saldo > 0) $situacao = 'A receber';
        $jogadores[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'fichas_compradas' => floatval($row['fichas_compradas']),
            'fichas_devolvidas' => floatval($row['fichas_devolvidas']),
            'saldo_atual' => $saldo,
            'situacao' => $situacao
        ];
    }
}

// 7. Cálculos finais
$inventario_atual = $inventario['valor_abertura'] - $fichas_vendidas + $fichas_devolvidas + $rake_total + $total_bruto_caixinhas;
$receitas_totais = $rake_total + $total_cashback_caixinhas;
$saldo_operacional = $receitas_totais - $despesas_totais;

// 8. Diferença de inventário
$inventario_real = $inventario['inventario_real'];
$dif_inventario = ($inventario_real !== null) ? $inventario_real - $inventario_atual : null;

// 9. Resposta agregada
$response = [
    'success' => true,
    'inventario' => [
        'valor_abertura' => $inventario['valor_abertura'],
        'inventario_atual' => $inventario_atual,
        'inventario_real' => $inventario_real,
        'diferenca' => $dif_inventario
    ],
    'receitas' => [
        'rake_total' => $rake_total,
        'cashback_caixinhas' => $total_cashback_caixinhas,
        'total' => $receitas_totais
    ],
    'despesas' => [
        'total' => $despesas_totais
    ],
    'caixinhas' => [
        'total_bruto' => $total_bruto_caixinhas,
        'total_liquido' => $total_bruto_caixinhas - $total_cashback_caixinhas,
        'lista' => $caixinhas
    ],
    'jogadores' => [
        'total' => count($jogadores),
        'lista' => $jogadores
    ],
    'saldo_operacional' => $saldo_operacional
];

echo json_encode($response);
exit; 