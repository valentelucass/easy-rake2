<?php
require_once __DIR__ . '/../src/api/db_connect.php';
$conn = getConnection();

function listar($conn, $status) {
    $sql = "SELECT aa.id, aa.tipo, aa.status, aa.data_solicitacao, aa.data_decisao, us.nome as funcionario_nome, us.cpf as funcionario_cpf, u.nome as unidade_nome FROM aprovacoes_acesso aa JOIN funcionarios f ON aa.funcionario_id = f.id JOIN usuarios us ON f.usuario_id = us.id JOIN unidades u ON f.unidade_id = u.id WHERE aa.status = ? ORDER BY aa.data_solicitacao DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $dados;
}

$pendentes = listar($conn, 'Pendente');
$aprovadas = listar($conn, 'Aprovado');
$rejeitadas = listar($conn, 'Rejeitado');

function renderTabela($dados, $titulo) {
    echo "<h2>$titulo</h2>";
    if (empty($dados)) {
        echo '<p><i>Nenhum registro encontrado.</i></p>';
        return;
    }
    echo '<table border="1" cellpadding="6" style="border-collapse:collapse; margin-bottom:2em;">';
    echo '<tr><th>ID</th><th>Tipo</th><th>Nome</th><th>CPF</th><th>Unidade</th><th>Status</th><th>Data Solicitação</th><th>Data Decisão</th></tr>';
    foreach ($dados as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['funcionario_nome']) . '</td>';
        echo '<td>' . htmlspecialchars($row['funcionario_cpf']) . '</td>';
        echo '<td>' . htmlspecialchars($row['unidade_nome']) . '</td>';
        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
        echo '<td>' . htmlspecialchars($row['data_solicitacao']) . '</td>';
        echo '<td>' . htmlspecialchars($row['data_decisao'] ?? '-') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Listagem de Aprovações de Acesso</title></head>
<body style="font-family:sans-serif; background:#222; color:#eee; padding:2em;">
<h1>Listagem de Aprovações de Acesso</h1>
<?php
renderTabela($pendentes, 'Pendentes');
renderTabela($aprovadas, 'Aprovadas');
renderTabela($rejeitadas, 'Rejeitadas');
?>
</body>
</html> 