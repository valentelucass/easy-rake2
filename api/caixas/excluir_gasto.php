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
    send_json_response(false, 'Acesso negado. Apenas Gestores e Caixas podem excluir despesas.');
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

    // Verificar se o gasto existe e pertence ao caixa aberto do usuário
    $stmt = $pdo->prepare("
        SELECT g.*, c.operador_id
        FROM gastos g
        JOIN caixas c ON g.caixa_id = c.id
        WHERE g.id = ? AND c.operador_id = ? AND c.status = 'Aberto'
    ");
    $stmt->execute([$gasto_id, $_SESSION['user_id']]);
    $gasto = $stmt->fetch();

    if (!$gasto) {
        send_json_response(false, 'Despesa não encontrada ou não pertence ao seu caixa');
        exit;
    }

    // Excluir o gasto
    $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = ?");
    $stmt->execute([$gasto_id]);

    if ($stmt->rowCount() > 0) {
        send_json_response(true, 'Despesa excluída com sucesso');
    } else {
        send_json_response(false, 'Erro ao excluir despesa');
    }

} catch (PDOException $e) {
    error_log("Erro ao excluir gasto: " . $e->getMessage());
    send_json_response(false, 'Erro interno do servidor');
} catch (Exception $e) {
    error_log("Erro inesperado ao excluir gasto: " . $e->getMessage());
    send_json_response(false, 'Erro inesperado');
}
?> 