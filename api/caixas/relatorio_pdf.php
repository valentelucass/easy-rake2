<?php
require_once '../db_connect.php';
require_once '../../vendor/autoload.php'; // mPDF

$caixa_id = isset($_GET['caixa_id']) ? intval($_GET['caixa_id']) : 0;
if (!$caixa_id) {
    die('ID do caixa não informado.');
}

// Buscar dados do endpoint de compilação
function getDadosCaixa($caixa_id, $conn) {
    // Movimentações
    $sql = "SELECT m.*, u.nome as operador_nome
        FROM movimentacoes m
        LEFT JOIN usuarios u ON m.operador_id = u.id
        WHERE m.caixa_id = ?
        ORDER BY m.data_movimentacao ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $caixa_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $movimentacoes = [];
    $total_entradas = 0;
    $total_saidas = 0;
    while ($row = $res->fetch_assoc()) {
        $movimentacoes[] = $row;
        if ($row['tipo'] === 'Entrada') {
            $total_entradas += floatval($row['valor']);
        } else {
            $total_saidas += floatval($row['valor']);
        }
    }
    // Caixa info
    $sql_caixa = "SELECT c.*, u.nome as operador_nome FROM caixas c LEFT JOIN usuarios u ON c.operador_id = u.id WHERE c.id = ?";
    $stmt2 = $conn->prepare($sql_caixa);
    $stmt2->bind_param('i', $caixa_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $caixa = $res2->fetch_assoc();
    // Transações jogadores
    $transacoes = [];
    if ($caixa) {
        $data_ini = $caixa['data_abertura'];
        $data_fim = $caixa['data_fechamento'] ?? date('Y-m-d H:i:s');
        $sql_trans = "SELECT t.*, j.nome as jogador_nome, j.status as jogador_status, u.nome as operador_nome
            FROM transacoes_jogadores t
            LEFT JOIN jogadores j ON t.jogador_id = j.id
            LEFT JOIN usuarios u ON t.operador_id = u.id
            WHERE t.data_transacao BETWEEN ? AND ?
            ORDER BY t.data_transacao ASC";
        $stmt3 = $conn->prepare($sql_trans);
        $stmt3->bind_param('ss', $data_ini, $data_fim);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        while ($row = $res3->fetch_assoc()) {
            $transacoes[] = $row;
        }
    }
    $saldo_final = isset($caixa['valor_final']) ? floatval($caixa['valor_final']) : (floatval($caixa['valor_inicial']) + $total_entradas - $total_saidas);
    return [
        'caixa' => $caixa,
        'movimentacoes' => $movimentacoes,
        'transacoes_jogadores' => $transacoes,
        'total_entradas' => $total_entradas,
        'total_saidas' => $total_saidas,
        'saldo_final' => $saldo_final
    ];
}

$dados = getDadosCaixa($caixa_id, $conn);

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$css = file_get_contents(__DIR__ . '/../../css/export/relatorio-caixa.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$html = '<h2>Relatório de Caixa #' . $caixa_id . '</h2>';
$html .= '<strong>Operador:</strong> ' . htmlspecialchars($dados['caixa']['operador_nome'] ?? '-') . '<br>';
$html .= '<strong>Data de Abertura:</strong> ' . htmlspecialchars($dados['caixa']['data_abertura'] ?? '-') . '<br>';
$html .= '<strong>Data de Fechamento:</strong> ' . htmlspecialchars($dados['caixa']['data_fechamento'] ?? '-') . '<br>';
$html .= '<strong>Valor Inicial:</strong> R$ ' . number_format($dados['caixa']['valor_inicial'],2,',','.') . '<br>';
$html .= '<strong>Valor Final:</strong> R$ ' . number_format($dados['saldo_final'],2,',','.') . '<br><br>';

$html .= '<h3>Movimentações</h3>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%"><thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Valor (R$)</th><th>Operador</th></tr></thead><tbody>';
foreach ($dados['movimentacoes'] as $mov) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($mov['data_movimentacao']) . '</td>';
    $html .= '<td>' . htmlspecialchars($mov['tipo']) . '</td>';
    $html .= '<td>' . htmlspecialchars($mov['descricao']) . '</td>';
    $html .= '<td>' . number_format($mov['valor'],2,',','.') . '</td>';
    $html .= '<td>' . htmlspecialchars($mov['operador_nome'] ?? '-') . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$html .= '<br><strong>Total de Entradas:</strong> R$ ' . number_format($dados['total_entradas'],2,',','.') . '<br>';
$html .= '<strong>Total de Saídas:</strong> R$ ' . number_format($dados['total_saidas'],2,',','.') . '<br>';
$html .= '<strong>Saldo Final:</strong> R$ ' . number_format($dados['saldo_final'],2,',','.') . '<br>';

$html .= '<h3>Transações de Jogadores</h3>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%"><thead><tr><th>Data</th><th>Jogador</th><th>Status</th><th>Tipo</th><th>Valor (R$)</th><th>Operador</th><th>Observação</th></tr></thead><tbody>';
foreach ($dados['transacoes_jogadores'] as $t) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($t['data_transacao']) . '</td>';
    $html .= '<td>' . htmlspecialchars($t['jogador_nome']) . '</td>';
    $html .= '<td>' . htmlspecialchars($t['jogador_status']) . '</td>';
    $html .= '<td>' . htmlspecialchars($t['tipo']) . '</td>';
    $html .= '<td>' . number_format($t['valor'],2,',','.') . '</td>';
    $html .= '<td>' . htmlspecialchars($t['operador_nome'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($t['observacao'] ?? '-') . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->SetTitle('Relatorio_Caixa_' . $caixa_id);
$mpdf->Output('Relatorio_Caixa_' . $caixa_id . '.pdf', 'D'); 