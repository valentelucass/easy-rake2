<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Gestor') {
    echo json_encode(['success' => false, 'message' => 'Acesso restrito ao gestor.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$tipo = $input['tipo'] ?? '';
$id = $input['id'] ?? '';
$acao = $input['acao'] ?? '';

if (!in_array($tipo, ['Sanger', 'Caixa']) || !$id || !in_array($acao, ['aprovar', 'rejeitar', 'remover'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos.']);
    exit;
}

try {
    if ($tipo === 'Sanger') {
        if ($acao === 'remover') {
            $stmt = $conn->prepare("UPDATE associacoes_usuario_unidade SET status_aprovacao = 'Removido', data_aprovacao = NOW() WHERE id = ? AND status_aprovacao = 'Aprovado'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Não foi possível remover. O sanger pode já ter sido removido ou não estava aprovado.']);
                exit;
            }
            echo json_encode(['success' => true, 'message' => 'Sanger removido da unidade com sucesso.']);
        } else {
            $novo_status = $acao === 'aprovar' ? 'Aprovado' : 'Rejeitado';
            $stmt = $conn->prepare("UPDATE associacoes_usuario_unidade SET status_aprovacao = ?, data_aprovacao = NOW() WHERE id = ?");
            $stmt->bind_param('si', $novo_status, $id);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Associação não encontrada ou o status já foi atualizado.']);
                exit;
            }
            echo json_encode(['success' => true, 'message' => 'Sanger ' . ($acao === 'aprovar' ? 'aprovado' : 'rejeitado') . ' com sucesso.']);
        }
    } elseif ($tipo === 'Caixa') {
        $novo_status = $acao === 'aprovar' ? 'Aprovado' : 'Rejeitado';
        $stmt = $conn->prepare("UPDATE aprovacoes SET status = ?, data_aprovacao = NOW(), aprovador_id = ? WHERE id = ?");
        $stmt->bind_param('sii', $novo_status, $_SESSION['user_id'], $id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Aprovação de caixa não encontrada ou já foi atualizada.']);
            exit;
        }
        echo json_encode(['success' => true, 'message' => 'Caixa ' . ($acao === 'aprovar' ? 'aprovada' : 'rejeitada') . ' com sucesso.']);
    }
} catch (Exception $e) {
    error_log('Erro na ação de aprovação: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro ao processar a solicitação.']);
}
$conn->close();