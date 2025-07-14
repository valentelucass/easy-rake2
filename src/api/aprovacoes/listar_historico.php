<?php
/**
 * API para Listar Histórico de Aprovações Operacionais - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';
require_once '../../../../api/db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canManageUsers()) sendForbidden('Apenas gestores podem ver histórico de aprovações');

try {
    $sql = "
        SELECT ao.*, f.nome as funcionario_solicitante, u.nome as unidade_nome
        FROM aprovacoes_operacionais ao
        JOIN funcionarios f ON ao.funcionario_solicitante_id = f.id
        JOIN unidades u ON f.unidade_id = u.id
        WHERE ao.status IN ('Aprovado', 'Rejeitado')
        ORDER BY ao.data_solicitacao DESC
    ";
    
    $result = $conn->query($sql);
    $aprovacoes = [];
    while ($row = $result->fetch_assoc()) {
        $aprovacoes[] = $row;
    }
    
    sendResponse(true, 'Histórico de aprovações listado com sucesso', 200, $aprovacoes);
    
} catch (Exception $e) {
    sendInternalError('Erro ao listar histórico de aprovações: ' . $e->getMessage());
}
$conn->close(); 