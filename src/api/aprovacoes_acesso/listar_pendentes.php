<?php
/**
 * API para Listar Aprovações de Acesso Pendentes - Easy Rake 2.0
 */
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../utils/auth.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../db_connect.php';
$conn = getConnection();

if (!isAuthenticated()) sendUnauthorized();
if (!canApprove()) sendForbidden('Apenas gestores podem ver aprovações de acesso pendentes');

try {
    error_log('[DEBUG][listar_pendentes] INICIO SQL');
    $sql = "SELECT aa.*, f.id as funcionario_id, u.nome as unidade_nome, us.nome as funcionario_nome, us.cpf as funcionario_cpf FROM aprovacoes_acesso aa JOIN funcionarios f ON aa.funcionario_id = f.id JOIN usuarios us ON f.usuario_id = us.id JOIN unidades u ON f.unidade_id = u.id WHERE aa.status = 'Pendente' ORDER BY aa.data_solicitacao DESC";
    error_log('[DEBUG][listar_pendentes] SQL: ' . $sql);
    $result = $conn->query($sql);
    if ($result === false) error_log('[DEBUG][listar_pendentes] SQL ERROR: ' . $conn->error);
    $aprovacoes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $aprovacoes[] = [
                'id' => $row['id'],
                'tipo' => $row['tipo'],
                'solicitante' => $row['funcionario_nome'],
                'cpf' => $row['funcionario_cpf'],
                'data_solicitacao' => $row['data_solicitacao'],
                'status' => $row['status'],
                'unidade' => $row['unidade_nome'],
                // Adicione outros campos necessários para o frontend aqui
            ];
        }
    }
    sendList($aprovacoes, count($aprovacoes));
} catch (Exception $e) {
    error_log('[DEBUG][listar_pendentes] EXCEPTION: ' . $e->getMessage());
    sendInternalError('Erro ao listar aprovações de acesso: ' . $e->getMessage());
}
$conn->close();
?> 