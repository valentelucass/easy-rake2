<?php
require_once '../db_connect.php';
require_once '../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Relatório Caixa');
$row = 1;
// Cabeçalho
$sheet->setCellValue('A'.$row, 'Relatório de Caixa #'.$caixa_id);
$sheet->mergeCells('A'.$row.':G'.$row);
$sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('E11D48');
$row++;
$sheet->setCellValue('A'.$row, 'Operador:');
$sheet->setCellValue('B'.$row, $dados['caixa']['operador_nome'] ?? '-');
$row++;
$sheet->setCellValue('A'.$row, 'Data de Abertura:');
$sheet->setCellValue('B'.$row, $dados['caixa']['data_abertura'] ?? '-');
$row++;
$sheet->setCellValue('A'.$row, 'Data de Fechamento:');
$sheet->setCellValue('B'.$row, $dados['caixa']['data_fechamento'] ?? '-');
$row++;
$sheet->setCellValue('A'.$row, 'Valor Inicial:');
$sheet->setCellValue('B'.$row, number_format($dados['caixa']['valor_inicial'],2,',','.'));
$row++;
$sheet->setCellValue('A'.$row, 'Valor Final:');
$sheet->setCellValue('B'.$row, number_format($dados['saldo_final'],2,',','.'));
$row += 2;
// Movimentações
$sheet->setCellValue('A'.$row, 'Movimentações');
$sheet->mergeCells('A'.$row.':G'.$row);
$sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('E11D48');
$row++;
$headers = ['Data', 'Tipo', 'Descrição', 'Valor (R$)', 'Operador'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.$row, $h);
    $sheet->getStyle($col.$row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle($col.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E11D48');
    $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $col++;
}
$row++;
foreach ($dados['movimentacoes'] as $mov) {
    $sheet->setCellValue('A'.$row, $mov['data_movimentacao']);
    $sheet->setCellValue('B'.$row, $mov['tipo']);
    $sheet->setCellValue('C'.$row, $mov['descricao']);
    $sheet->setCellValue('D'.$row, number_format($mov['valor'],2,',','.'));
    $sheet->setCellValue('E'.$row, $mov['operador_nome'] ?? '-');
    foreach (range('A','E') as $col) {
        $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    $row++;
}
$sheet->setCellValue('C'.$row, 'Total de Entradas:');
$sheet->setCellValue('D'.$row, number_format($dados['total_entradas'],2,',','.'));
$row++;
$sheet->setCellValue('C'.$row, 'Total de Saídas:');
$sheet->setCellValue('D'.$row, number_format($dados['total_saidas'],2,',','.'));
$row++;
$sheet->setCellValue('C'.$row, 'Saldo Final:');
$sheet->setCellValue('D'.$row, number_format($dados['saldo_final'],2,',','.'));
$row += 2;
// Transações de Jogadores
$sheet->setCellValue('A'.$row, 'Transações de Jogadores');
$sheet->mergeCells('A'.$row.':G'.$row);
$sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('E11D48');
$row++;
$headers = ['Data', 'Jogador', 'Status', 'Tipo', 'Valor (R$)', 'Operador', 'Observação'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.$row, $h);
    $sheet->getStyle($col.$row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle($col.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E11D48');
    $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $col++;
}
$row++;
foreach ($dados['transacoes_jogadores'] as $t) {
    $sheet->setCellValue('A'.$row, $t['data_transacao']);
    $sheet->setCellValue('B'.$row, $t['jogador_nome']);
    $sheet->setCellValue('C'.$row, $t['jogador_status']);
    $sheet->setCellValue('D'.$row, $t['tipo']);
    $sheet->setCellValue('E'.$row, number_format($t['valor'],2,',','.'));
    $sheet->setCellValue('F'.$row, $t['operador_nome'] ?? '-');
    $sheet->setCellValue('G'.$row, $t['observacao'] ?? '-');
    foreach (range('A','G') as $col) {
        $sheet->getStyle($col.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    $row++;
}

// Ajustar largura das colunas
foreach (range('A','G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download
$filename = 'Relatorio_Caixa_' . $caixa_id . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 