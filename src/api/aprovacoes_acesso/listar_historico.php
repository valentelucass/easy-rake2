<?php
/**
 * API para Listar Histórico de Aprovações de Acesso - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

if (!isAuthenticated()) sendUnauthorized();
if (!canManageUsers()) sendForbidden('Apenas gestores podem ver histórico de aprovações de acesso');

try {
    error_log('[DEBUG][listar_historico] INICIO SQL');
    $sql = "SELECT h.id, h.aprovacao_id, h.status, h.data_acao, aa.tipo, aa.data_solicitacao, us.nome as solicitante, us.cpf, ga.nome as gestor_nome
            FROM aprovacoes_acesso_historico h
            JOIN aprovacoes_acesso aa ON h.aprovacao_id = aa.id
            JOIN funcionarios f ON h.funcionario_id = f.id
            JOIN usuarios us ON f.usuario_id = us.id
            LEFT JOIN funcionarios fg ON h.funcionario_aprovador_id = fg.id
            LEFT JOIN usuarios ga ON fg.usuario_id = ga.id
            ORDER BY h.data_acao DESC";
    error_log('[DEBUG][listar_historico] SQL: ' . $sql);
    $result = $conn->query($sql);
    if ($result === false) error_log('[DEBUG][listar_historico] SQL ERROR: ' . $conn->error);
    $historico = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $historico[] = [
                'id' => $row['id'],
                'tipo' => $row['tipo'],
                'solicitante' => $row['solicitante'],
                'cpf' => $row['cpf'],
                'status' => $row['status'],
                'data_solicitacao' => $row['data_solicitacao'],
                'data_decisao' => $row['data_acao'],
                'gestor_nome' => $row['gestor_nome']
            ];
        }
    }
    sendResponse(true, 'Histórico de aprovações de acesso listado com sucesso', 200, $historico);
} catch (Exception $e) {
    error_log('[DEBUG][listar_historico] EXCEPTION: ' . $e->getMessage());
    sendInternalError('Erro ao listar histórico de aprovações de acesso: ' . $e->getMessage());
}
$conn->close(); 