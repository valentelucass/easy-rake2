<?php
error_log('[DEBUG][acao.php] INICIO');
/**
 * API para Ações de Aprovação de Acesso - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

if (!isAuthenticated()) sendUnauthorized();
if (!canApprove()) sendForbidden('Apenas gestores podem aprovar/rejeitar acesso');

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
    $funcionarioAprovadorId = getCurrentFuncionarioId();

    // Atualiza status da aprovação
    $stmt = $conn->prepare("
        UPDATE aprovacoes_acesso 
        SET status = ?, gestor_id = ?, data_decisao = NOW() 
        WHERE id = ? AND status = 'Pendente'
    ");
    $stmt->bind_param('sii', $status, $funcionarioAprovadorId, $id);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    if ($stmt->affected_rows === 0) {
        sendNotFound('Aprovação de acesso não encontrada ou já processada');
    }

    // Busca dados da aprovação para histórico e permissões
    $stmt2 = $conn->prepare("SELECT funcionario_id FROM aprovacoes_acesso WHERE id = ?");
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $funcionarioId = $row2 ? $row2['funcionario_id'] : null;
    error_log('[DEBUG][acao.php] funcionarioId: ' . print_r($funcionarioId, true));
    $stmt2->close();

    // Atualiza permissões do funcionário
    if ($acao === 'aprovar' && $funcionarioId) {
        $stmtPerm = $conn->prepare("UPDATE funcionarios SET acesso_liberado = 1 WHERE id = ?");
        $stmtPerm->bind_param('i', $funcionarioId);
        $stmtPerm->execute();
        $stmtPerm->close();
    } else if ($acao === 'rejeitar' && $funcionarioId) {
        $stmtPerm = $conn->prepare("UPDATE funcionarios SET acesso_liberado = 0 WHERE id = ?");
        $stmtPerm->bind_param('i', $funcionarioId);
        $stmtPerm->execute();
        $stmtPerm->close();
    }

    // Registra histórico (exemplo: tabela aprovacoes_acesso_historico)
    if ($funcionarioId) {
        $stmtHist = $conn->prepare("INSERT INTO aprovacoes_acesso_historico (aprovacao_id, funcionario_id, status, data_acao, funcionario_aprovador_id) VALUES (?, ?, ?, NOW(), ?)");
        if (!$stmtHist) {
            error_log('[DEBUG][acao.php] Erro prepare INSERT historico: ' . $conn->error);
            sendInternalError('Erro ao preparar histórico: ' . $conn->error);
        }
        $stmtHist->bind_param('iisi', $id, $funcionarioId, $status, $funcionarioAprovadorId);
        if (!$stmtHist->execute()) {
            error_log('[DEBUG][acao.php] Erro execute INSERT historico: ' . $stmtHist->error);
            sendInternalError('Erro ao registrar histórico: ' . $stmtHist->error);
        }
        $stmtHist->close();
    } else {
        error_log('[DEBUG][acao.php] funcionarioId não encontrado, não foi possível registrar histórico.');
    }

    sendResponse(true, ucfirst($acao) . ' acesso com sucesso');
    
} catch (Exception $e) {
    sendInternalError('Erro ao processar aprovação de acesso: ' . $e->getMessage());
}
$conn->close();
?> 