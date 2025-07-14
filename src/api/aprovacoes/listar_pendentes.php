<?php
/**
 * API para Listar Aprovações Operacionais Pendentes - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';
require_once '../../../../api/db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canApprove()) sendForbidden('Apenas gestores podem ver aprovações pendentes');

try {
    $sql = "
        SELECT ao.*, f.nome as funcionario_solicitante, u.nome as unidade_nome
        FROM aprovacoes_operacionais ao
        JOIN funcionarios f ON ao.funcionario_solicitante_id = f.id
        JOIN unidades u ON f.unidade_id = u.id
        WHERE ao.status = 'Pendente'
        ORDER BY ao.data_solicitacao DESC
    ";
    
    $result = $conn->query($sql);
    $aprovacoes = [];
    while ($row = $result->fetch_assoc()) {
        $aprovacoes[] = $row;
    }
    
    sendList($aprovacoes, count($aprovacoes));
    
} catch (Exception $e) {
    sendInternalError('Erro ao listar aprovações: ' . $e->getMessage());
}
$conn->close();
?> 