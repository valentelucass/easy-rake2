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
    send_json_response(false, 'Acesso negado. Apenas Gestores e Caixas podem imprimir recibos.');
    exit;
}

try {
    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        send_json_response(false, 'ID da despesa é obrigatório');
        exit;
    }

    $gasto_id = intval($input['id']);

    // Buscar dados completos do gasto
    $stmt = $pdo->prepare("
        SELECT g.*, u.nome as operador, u.perfil, c.id as caixa_id, un.nome as unidade_nome
        FROM gastos g
        JOIN usuarios u ON g.operador_id = u.id
        JOIN caixas c ON g.caixa_id = c.id
        JOIN associacoes_usuario_unidade aau ON u.id = aau.id_usuario
        JOIN unidades un ON aau.id_unidade = un.id
        WHERE g.id = ? AND c.operador_id = ?
    ");
    $stmt->execute([$gasto_id, $_SESSION['user_id']]);
    $gasto = $stmt->fetch();

    if (!$gasto) {
        send_json_response(false, 'Despesa não encontrada ou não pertence ao seu caixa');
        exit;
    }

    // Aqui você implementaria a lógica de impressão térmica
    // Por enquanto, vamos simular o sucesso
    // Em uma implementação real, você enviaria os dados para a impressora térmica
    
    $dados_recibo = [
        'unidade' => $gasto['unidade_nome'],
        'descricao' => $gasto['descricao'],
        'valor' => number_format($gasto['valor'], 2, ',', '.'),
        'data_hora' => date('d/m/Y H:i', strtotime($gasto['data_hora'])),
        'operador' => $gasto['operador'],
        'perfil' => $gasto['perfil'],
        'caixa_id' => $gasto['caixa_id'],
        'observacoes' => $gasto['observacoes']
    ];

    // Log da impressão (para debug)
    error_log("Recibo de gasto enviado para impressão: " . json_encode($dados_recibo));

    send_json_response(true, 'Recibo enviado para impressão', $dados_recibo);

} catch (PDOException $e) {
    error_log("Erro ao imprimir recibo de gasto: " . $e->getMessage());
    send_json_response(false, 'Erro interno do servidor');
} catch (Exception $e) {
    error_log("Erro inesperado ao imprimir recibo de gasto: " . $e->getMessage());
    send_json_response(false, 'Erro inesperado');
}
?> 