<?php
session_start();
require_once '../db_connect.php';
require_once '../utils/response.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    send_json_response(false, 'Usuário não autenticado');
    exit;
}

// Verificar permissão (apenas Gestor e Caixa)
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    send_json_response(false, 'Acesso negado. Apenas Gestores e Caixas podem visualizar despesas.');
    exit;
}

try {
    // Buscar o caixa aberto atual do usuário
    $stmt = $pdo->prepare("
        SELECT id FROM caixas 
        WHERE operador_id = ? AND status = 'Aberto' 
        ORDER BY data_abertura DESC 
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $caixa = $stmt->fetch();

    if (!$caixa) {
        send_json_response(true, 'Nenhum caixa aberto', []);
        exit;
    }

    // Buscar gastos do caixa atual
    $stmt = $pdo->prepare("
        SELECT g.*, u.nome as operador, u.perfil
        FROM gastos g
        JOIN usuarios u ON g.operador_id = u.id
        WHERE g.caixa_id = ?
        ORDER BY g.data_hora DESC
    ");
    $stmt->execute([$caixa['id']]);
    $gastos = $stmt->fetchAll();

    send_json_response(true, 'Gastos carregados com sucesso', $gastos);

} catch (PDOException $e) {
    error_log("Erro ao listar gastos: " . $e->getMessage());
    send_json_response(false, 'Erro interno do servidor');
} catch (Exception $e) {
    error_log("Erro inesperado ao listar gastos: " . $e->getMessage());
    send_json_response(false, 'Erro inesperado');
}
?> 