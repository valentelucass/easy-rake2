<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../utils/response.php';

session_start();

// Verificação de autenticação e perfil
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    send_json_response(false, 'Usuário não autenticado.', null, null, 401);
    exit;
}

$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!in_array($perfil_usuario, ['Gestor', 'Caixa'])) {
    send_json_response(false, 'Acesso negado. Perfil não autorizado.', null, null, 403);
    exit;
}

$id_caixa = isset($_GET['id_caixa']) ? intval($_GET['id_caixa']) : 0;

if (!$id_caixa) {
    send_json_response(false, 'ID do caixa não fornecido.');
    exit;
}

try {
    // 1. INVENTÁRIO
    $stmt = $conn->prepare("SELECT valor_abertura FROM caixas WHERE id = ?");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $caixa_result = $stmt->get_result()->fetch_assoc();
    $valor_abertura = $caixa_result['valor_abertura'] ?? 0;

    // Calcular Fichas Vendidas e Devolvidas
    $stmt = $conn->prepare("SELECT SUM(CASE WHEN tipo_transacao = 'venda' THEN valor_total ELSE 0 END) as total_vendido, SUM(CASE WHEN tipo_transacao = 'devolucao' THEN valor_total ELSE 0 END) as total_devolvido FROM transacoes_fichas WHERE id_caixa = ?");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $fichas_result = $stmt->get_result()->fetch_assoc();
    $fichas_vendidas = $fichas_result['total_vendido'] ?? 0;
    $fichas_devolvidas = $fichas_result['total_devolvido'] ?? 0;

    // Calcular Rake Total
    $stmt = $conn->prepare("SELECT SUM(valor) as total_rake FROM rake WHERE id_caixa = ?");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $rake_result = $stmt->get_result()->fetch_assoc();
    $rake_total = $rake_result['total_rake'] ?? 0;

    // Calcular Caixinhas (valor bruto)
    $stmt = $conn->prepare("SELECT SUM(valor) as total_bruto_caixinhas FROM caixinhas_inclusoes WHERE id_caixa = ?");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $caixinhas_result = $stmt->get_result()->fetch_assoc();
    $total_bruto_caixinhas = $caixinhas_result['total_bruto_caixinhas'] ?? 0;

    // Calcular Inventário Atual
    $inventario_atual = $valor_abertura - $fichas_vendidas + $fichas_devolvidas + $rake_total + $total_bruto_caixinhas;

    // 2. RECEITAS
    $stmt = $conn->prepare("SELECT SUM(c.valor_total * (cx.percentual_cashback / 100)) as total_cashback FROM caixinhas_inclusoes c JOIN caixinhas cx ON c.id_caixinha = cx.id WHERE c.id_caixa = ?");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $cashback_result = $stmt->get_result()->fetch_assoc();
    $cashback_caixinhas = $cashback_result['total_cashback'] ?? 0;

    $receitas_totais = $rake_total + $cashback_caixinhas;

    // 3. DESPESAS
    $stmt = $conn->prepare("SELECT SUM(valor) as total_despesas FROM gastos WHERE id_caixa = ? AND status = 'Aprovado'");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $despesas_result = $stmt->get_result()->fetch_assoc();
    $despesas_totais = $despesas_result['total_despesas'] ?? 0;

    // 4. CAIXINHAS
    $total_liquido_caixinhas = $total_bruto_caixinhas - $cashback_caixinhas;

    // Buscar detalhes de cada caixinha para valor por participante
    $stmt = $conn->prepare("SELECT cx.nome, cx.num_participantes, SUM(ci.valor) as valor_bruto_caixinha FROM caixinhas_inclusoes ci JOIN caixinhas cx ON ci.id_caixinha = cx.id WHERE ci.id_caixa = ? GROUP BY cx.id");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $caixinhas_detalhes_result = $stmt->get_result();
    $caixinhas_detalhes = [];
    while ($row = $caixinhas_detalhes_result->fetch_assoc()) {
        $valor_liquido_caixinha = $row['valor_bruto_caixinha'] * (1 - ($cashback_result['total_cashback'] > 0 ? ($cashback_caixinhas / $total_bruto_caixinhas) : 0));
        $valor_por_participante = $row['num_participantes'] > 0 ? $valor_liquido_caixinha / $row['num_participantes'] : 0;
        $caixinhas_detalhes[] = [
            'nome' => $row['nome'],
            'valor_bruto' => $row['valor_bruto_caixinha'],
            'valor_por_participante' => $valor_por_participante
        ];
    }

    // 5. JOGADORES ATIVOS
    $stmt = $conn->prepare("SELECT j.id, j.nome, SUM(CASE WHEN tf.tipo_transacao = 'venda' THEN tf.valor_total ELSE 0 END) as total_comprado, SUM(CASE WHEN tf.tipo_transacao = 'devolucao' THEN tf.valor_total ELSE 0 END) as total_devolvido FROM jogadores j JOIN transacoes_fichas tf ON j.id = tf.id_jogador WHERE tf.id_caixa = ? GROUP BY j.id, j.nome");
    $stmt->bind_param('i', $id_caixa);
    $stmt->execute();
    $jogadores_result = $stmt->get_result();
    $jogadores_ativos = [];
    while ($row = $jogadores_result->fetch_assoc()) {
        $saldo_jogador = $row['total_devolvido'] - $row['total_comprado'];
        $situacao = 'Em dia';
        if ($saldo_jogador > 0) {
            $situacao = 'A receber';
        } elseif ($saldo_jogador < 0) {
            $situacao = 'Devedor';
        }
        $jogadores_ativos[] = [
            'nome' => $row['nome'],
            'fichas_compradas' => $row['total_comprado'],
            'fichas_devolvidas' => $row['total_devolvido'],
            'saldo_atual' => $saldo_jogador,
            'situacao' => $situacao
        ];
    }
    $total_jogadores_ativos = count($jogadores_ativos);

    // 6. SALDO OPERACIONAL
    $saldo_operacional = $receitas_totais - $despesas_totais;

    $response_data = [
        'inventario' => [
            'valor_abertura' => $valor_abertura,
            'inventario_atual_calculado' => $inventario_atual,
            'fichas_vendidas' => $fichas_vendidas,
            'fichas_devolvidas' => $fichas_devolvidas
        ],
        'receitas' => [
            'rake_total' => $rake_total,
            'cashback_caixinhas' => $cashback_caixinhas,
            'receitas_totais' => $receitas_totais
        ],
        'despesas' => [
            'despesas_totais' => $despesas_totais
        ],
        'caixinhas' => [
            'total_bruto' => $total_bruto_caixinhas,
            'total_liquido' => $total_liquido_caixinhas,
            'detalhes' => $caixinhas_detalhes
        ],
        'jogadores' => [
            'total_jogadores_ativos' => $total_jogadores_ativos,
            'lista' => $jogadores_ativos
        ],
        'saldo_operacional' => [
            'saldo_atual_clube' => $saldo_operacional
        ]
    ];

    send_json_response(true, 'Estatísticas da sessão carregadas.', $response_data);

} catch (Exception $e) {
    error_log('Erro ao buscar estatísticas da sessão: ' . $e->getMessage());
    send_json_response(false, 'Erro ao buscar estatísticas da sessão.', null, $e->getMessage());
}

$conn->close();
?>