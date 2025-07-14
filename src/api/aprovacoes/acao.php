<?php
/**
 * API para Ações de Aprovação Operacional - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../testes-diagnosticos/utils/auth.php';
require_once __DIR__ . '/../../../testes-diagnosticos/utils/response.php';
require_once '../../../../api/db_connect.php';

if (!isAuthenticated()) sendUnauthorized();
if (!canApprove()) sendForbidden('Apenas gestores podem aprovar/rejeitar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Método não permitido', 405);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id']) || !isset($input['acao'])) {
        sendValidationError('Dados inválidos');
    }
    
    $id = intval($input['id']);
    $acao = $input['acao']; // 'aprovar' ou 'rejeitar'
    
    if (!in_array($acao, ['aprovar', 'rejeitar'])) {
        sendValidationError('Ação inválida');
    }
    
    $status = ($acao === 'aprovar') ? 'Aprovado' : 'Rejeitado';
    
    $stmt = $conn->prepare("
        UPDATE aprovacoes_operacionais 
        SET status = ?, funcionario_aprovador_id = ?, data_aprovacao = NOW() 
        WHERE id = ? AND status = 'Pendente'
    ");
    
    $stmt->bind_param('sii', $status, getCurrentFuncionarioId(), $id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        sendNotFound('Aprovação não encontrada ou já processada');
    }
    
    sendApproval($acao === 'aprovar', ucfirst($acao) . ' com sucesso');
    
} catch (Exception $e) {
    sendInternalError('Erro ao processar aprovação: ' . $e->getMessage());
}
$conn->close();
?> 