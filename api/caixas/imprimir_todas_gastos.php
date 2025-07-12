<?php
session_start();
require_once '../db_connect.php';
require_once '../utils/response.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    send_json_response(false, 'Usuário não autenticado');
    exit;
}

// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Método não permitido');
    exit;
}

// Verificar permissão (apenas Gestor e Caixa)
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    send_json_response(false, 'Acesso negado. Apenas Gestores e Caixas podem imprimir relatórios.');
    exit;
}

try {
    // Buscar o caixa aberto atual do usuário
    $stmt = $pdo->prepare("
        SELECT c.*, un.nome as unidade_nome
        FROM caixas c
        JOIN associacoes_usuario_unidade aau ON c.operador_id = aau.id_usuario
        JOIN unidades un ON aau.id_unidade = un.id
        WHERE c.operador_id = ? AND c.status = 'Aberto' 
        ORDER BY c.data_abertura DESC 
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $caixa = $stmt->fetch();

    if (!$caixa) {
        send_json_response(false, 'Nenhum caixa aberto encontrado');
        exit;
    }

    // Buscar todos os gastos do caixa atual
    $stmt = $pdo->prepare("
        SELECT g.*, u.nome as operador, u.perfil
        FROM gastos g
        JOIN usuarios u ON g.operador_id = u.id
        WHERE g.caixa_id = ?
        ORDER BY g.data_hora ASC
    ");
    $stmt->execute([$caixa['id']]);
    $gastos = $stmt->fetchAll();

    if (empty($gastos)) {
        send_json_response(false, 'Nenhuma despesa encontrada para impressão');
        exit;
    }

    // Calcular total
    $total = array_sum(array_column($gastos, 'valor'));

    // Preparar dados para impressão
    $dados_relatorio = [
        'unidade' => $caixa['unidade_nome'],
        'caixa_id' => $caixa['id'],
        'data_abertura' => date('d/m/Y H:i', strtotime($caixa['data_abertura'])),
        'operador_caixa' => $_SESSION['nome'] ?? 'Operador',
        'total_despesas' => number_format($total, 2, ',', '.'),
        'quantidade_despesas' => count($gastos),
        'despesas' => []
    ];

    foreach ($gastos as $gasto) {
        $dados_relatorio['despesas'][] = [
            'descricao' => $gasto['descricao'],
            'valor' => number_format($gasto['valor'], 2, ',', '.'),
            'data_hora' => date('d/m/Y H:i', strtotime($gasto['data_hora'])),
            'operador' => $gasto['operador'],
            'perfil' => $gasto['perfil'],
            'observacoes' => $gasto['observacoes']
        ];
    }

    // Log da impressão (para debug)
    error_log("Relatório de todas as despesas enviado para impressão: " . json_encode($dados_relatorio));

    send_json_response(true, 'Relatório enviado para impressão', $dados_relatorio);

} catch (PDOException $e) {
    error_log("Erro ao imprimir relatório de gastos: " . $e->getMessage());
    send_json_response(false, 'Erro interno do servidor');
} catch (Exception $e) {
    error_log("Erro inesperado ao imprimir relatório de gastos: " . $e->getMessage());
    send_json_response(false, 'Erro inesperado');
}
?> 