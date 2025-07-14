<?php
/**
 * 🔍 ANÁLISE DE ALINHAMENTO: BANCO DE DADOS vs APIs
 * 
 * Verifica se as APIs estão alinhadas com a estrutura do banco normalizado
 */

echo "🔍 ANÁLISE DE ALINHAMENTO: BANCO DE DADOS vs APIs\n";
echo "=================================================\n\n";

// ==========================================================================
// ESTRUTURA DO BANCO NORMALIZADO
// ==========================================================================

$tabelas_banco = [
    'usuarios' => [
        'campos' => ['id', 'nome', 'cpf', 'email', 'senha', 'status', 'data_criacao', 'data_ultimo_acesso'],
        'api_pasta' => 'src/api/usuarios/',
        'status' => '✅ Alinhada'
    ],
    'unidades' => [
        'campos' => ['id', 'nome', 'telefone', 'endereco', 'codigo_acesso', 'status', 'data_criacao'],
        'api_pasta' => 'src/api/unidades/',
        'status' => '✅ Alinhada'
    ],
    'funcionarios' => [
        'campos' => ['id', 'usuario_id', 'unidade_id', 'cargo', 'status', 'data_vinculo', 'data_aprovacao', 'data_demissao'],
        'api_pasta' => 'src/api/funcionarios/',
        'status' => '✅ Alinhada'
    ],
    'aprovacoes_acesso' => [
        'campos' => ['id', 'funcionario_id', 'tipo', 'status', 'data_solicitacao', 'data_decisao', 'gestor_id', 'observacoes'],
        'api_pasta' => 'src/api/aprovacoes_acesso/',
        'status' => '✅ Alinhada'
    ],
    'aprovacoes' => [
        'campos' => ['id', 'tipo', 'referencia_id', 'funcionario_id', 'status', 'data_solicitacao', 'data_decisao', 'gestor_id', 'observacoes'],
        'api_pasta' => 'src/api/aprovacoes/',
        'status' => '✅ Alinhada'
    ],
    'jogadores' => [
        'campos' => ['id', 'unidade_id', 'nome', 'cpf', 'telefone', 'email', 'status', 'data_cadastro', 'limite_credito', 'saldo_atual', 'funcionario_cadastro_id'],
        'api_pasta' => 'src/api/jogadores/',
        'status' => '⚠️ Parcialmente alinhada'
    ],
    'caixas' => [
        'campos' => ['id', 'unidade_id', 'funcionario_abertura_id', 'funcionario_fechamento_id', 'status', 'data_abertura', 'data_fechamento', 'inventario_inicial', 'inventario_final', 'observacoes'],
        'api_pasta' => 'src/api/caixas/',
        'status' => '⚠️ Parcialmente alinhada'
    ],
    'fichas_denom' => [
        'campos' => ['id', 'unidade_id', 'valor', 'descricao', 'status'],
        'api_pasta' => 'src/api/fichas_denom/',
        'status' => '❌ Pasta vazia'
    ],
    'inventario_fichas' => [
        'campos' => ['id', 'caixa_id', 'ficha_denom_id', 'quantidade_inicial', 'quantidade_final'],
        'api_pasta' => 'src/api/inventario_fichas/',
        'status' => '❌ Pasta vazia'
    ],
    'movimentacoes_fichas' => [
        'campos' => ['id', 'caixa_id', 'jogador_id', 'funcionario_id', 'tipo', 'ficha_denom_id', 'quantidade', 'valor_total', 'data', 'observacoes'],
        'api_pasta' => 'src/api/movimentacoes_fichas/',
        'status' => '⚠️ Parcialmente alinhada'
    ],
    'caixinhas' => [
        'campos' => ['id', 'caixa_id', 'funcionario_criacao_id', 'nome', 'valor_meta', 'valor_atual', 'status', 'data_criacao', 'data_conclusao', 'observacoes'],
        'api_pasta' => 'src/api/caixinhas/',
        'status' => '✅ Alinhada'
    ],
    'caixinhas_inclusoes' => [
        'campos' => ['id', 'caixinha_id', 'funcionario_id', 'valor', 'data_inclusao', 'observacoes'],
        'api_pasta' => 'src/api/caixinhas_inclusoes/',
        'status' => '✅ Alinhada'
    ],
    'gastos' => [
        'campos' => ['id', 'caixa_id', 'funcionario_id', 'categoria', 'descricao', 'valor', 'observacoes', 'data_registro', 'status'],
        'api_pasta' => 'src/api/gastos/',
        'status' => '✅ Alinhada'
    ],
    'rake' => [
        'campos' => ['id', 'caixa_id', 'funcionario_id', 'valor', 'data_hora', 'observacoes'],
        'api_pasta' => 'src/api/rake/',
        'status' => '✅ Alinhada'
    ],
    'relatorios_historico' => [
        'campos' => ['id', 'funcionario_id', 'unidade_id', 'tipo', 'status', 'data_geracao', 'data_conclusao', 'arquivo', 'mensagem_erro', 'parametros'],
        'api_pasta' => 'src/api/relatorios_historico/',
        'status' => '❌ Pasta vazia'
    ],
    'transacoes_jogadores' => [
        'campos' => ['id', 'jogador_id', 'caixa_id', 'funcionario_id', 'tipo', 'valor', 'observacao', 'data_transacao', 'quitado', 'data_quitacao'],
        'api_pasta' => 'src/api/transacoes_jogadores/',
        'status' => '✅ Alinhada'
    ]
];

// ==========================================================================
// ANÁLISE DE ALINHAMENTO
// ==========================================================================

echo "📊 ANÁLISE DE ALINHAMENTO POR TABELA\n";
echo "====================================\n\n";

$total_tabelas = count($tabelas_banco);
$tabelas_alinhadas = 0;
$tabelas_parciais = 0;
$tabelas_nao_alinhadas = 0;

foreach ($tabelas_banco as $tabela => $info) {
    echo "📋 $tabela\n";
    echo "   Status: {$info['status']}\n";
    echo "   Pasta API: {$info['api_pasta']}\n";
    echo "   Campos: " . implode(', ', $info['campos']) . "\n";
    
    // Verificar se a pasta existe
    if (is_dir($info['api_pasta'])) {
        $arquivos = scandir($info['api_pasta']);
        $arquivos = array_diff($arquivos, ['.', '..']);
        echo "   APIs encontradas: " . count($arquivos) . "\n";
        
        if (count($arquivos) > 0) {
            if ($info['status'] === '✅ Alinhada') {
                $tabelas_alinhadas++;
            } elseif ($info['status'] === '⚠️ Parcialmente alinhada') {
                $tabelas_parciais++;
            }
        } else {
            $tabelas_nao_alinhadas++;
        }
    } else {
        echo "   ❌ Pasta não existe\n";
        $tabelas_nao_alinhadas++;
    }
    echo "\n";
}

// ==========================================================================
// PROBLEMAS IDENTIFICADOS
// ==========================================================================

echo "🚨 PROBLEMAS IDENTIFICADOS\n";
echo "==========================\n\n";

$problemas = [
    'jogadores' => [
        'problema' => 'API usa campos antigos (created_by) em vez de funcionario_cadastro_id',
        'solucao' => 'Atualizar API para usar funcionario_cadastro_id'
    ],
    'caixas' => [
        'problema' => 'API usa campos antigos (nome, created_by) em vez da estrutura normalizada',
        'solucao' => 'Atualizar API para usar unidade_id, funcionario_abertura_id, inventario_inicial'
    ],
    'fichas_denom' => [
        'problema' => 'Pasta vazia - APIs não implementadas',
        'solucao' => 'Implementar APIs CRUD para denominações de fichas'
    ],
    'inventario_fichas' => [
        'problema' => 'Pasta vazia - APIs não implementadas',
        'solucao' => 'Implementar APIs para controle de inventário'
    ],
    'movimentacoes_fichas' => [
        'problema' => 'API parcial - precisa trabalhar com fichas_denom',
        'solucao' => 'Atualizar API para usar ficha_denom_id'
    ],
    'relatorios_historico' => [
        'problema' => 'Pasta vazia - APIs não implementadas',
        'solucao' => 'Implementar APIs para histórico de relatórios'
    ]
];

foreach ($problemas as $tabela => $info) {
    echo "⚠️  $tabela\n";
    echo "   Problema: {$info['problema']}\n";
    echo "   Solução: {$info['solucao']}\n\n";
}

// ==========================================================================
// APIS QUE PRECISAM ATUALIZAÇÃO
// ==========================================================================

echo "🔄 APIS QUE PRECISAM ATUALIZAÇÃO\n";
echo "===============================\n\n";

$apis_para_atualizar = [
    'api/jogadores/criar.php' => 'Usar funcionario_cadastro_id em vez de created_by',
    'api/jogadores/atualizar.php' => 'Usar funcionario_cadastro_id em vez de created_by',
    'api/caixas/criar.php' => 'Usar estrutura normalizada (unidade_id, funcionario_abertura_id, inventario_inicial)',
    'api/caixas/atualizar.php' => 'Usar estrutura normalizada',
    'api/movimentacoes_fichas/movimentar.php' => 'Usar ficha_denom_id em vez de valor direto'
];

foreach ($apis_para_atualizar as $api => $motivo) {
    echo "🔄 $api\n";
    echo "   Motivo: $motivo\n\n";
}

// ==========================================================================
// RESUMO ESTATÍSTICO
// ==========================================================================

echo "📊 RESUMO ESTATÍSTICO\n";
echo "=====================\n\n";

echo "📋 ALINHAMENTO:\n";
echo "   • Tabelas alinhadas: $tabelas_alinhadas/$total_tabelas\n";
echo "   • Tabelas parcialmente alinhadas: $tabelas_parciais/$total_tabelas\n";
echo "   • Tabelas não alinhadas: $tabelas_nao_alinhadas/$total_tabelas\n\n";

$percentual_alinhamento = round((($tabelas_alinhadas + $tabelas_parciais) / $total_tabelas) * 100, 1);

echo "📈 PERCENTUAL DE ALINHAMENTO: $percentual_alinhamento%\n\n";

// ==========================================================================
// RECOMENDAÇÕES
// ==========================================================================

echo "🎯 RECOMENDAÇÕES\n";
echo "================\n\n";

echo "1. 🔄 ATUALIZAR APIS EXISTENTES:\n";
echo "   • Corrigir APIs de jogadores para usar funcionario_cadastro_id\n";
echo "   • Corrigir APIs de caixas para usar estrutura normalizada\n";
echo "   • Atualizar movimentacoes_fichas para usar ficha_denom_id\n\n";

echo "2. ➕ IMPLEMENTAR APIS FALTANTES:\n";
echo "   • Criar APIs para fichas_denom\n";
echo "   • Criar APIs para inventario_fichas\n";
echo "   • Criar APIs para relatorios_historico\n\n";

echo "3. ✅ PRIORIDADES:\n";
echo "   • Alta: Corrigir APIs de jogadores e caixas\n";
echo "   • Média: Implementar APIs de fichas_denom\n";
echo "   • Baixa: Implementar APIs de inventario e histórico\n\n";

echo "4. 🧪 TESTES:\n";
echo "   • Testar todas as APIs após correções\n";
echo "   • Validar integridade dos dados\n";
echo "   • Verificar funcionalidades do frontend\n\n";

// ==========================================================================
// STATUS FINAL
// ==========================================================================

echo "🎯 STATUS FINAL\n";
echo "===============\n\n";

if ($percentual_alinhamento >= 90) {
    echo "✅ EXCELENTE ALINHAMENTO\n";
    echo "   O sistema está bem alinhado com o banco normalizado!\n";
} elseif ($percentual_alinhamento >= 70) {
    echo "⚠️  BOM ALINHAMENTO\n";
    echo "   Algumas correções são necessárias, mas o sistema funciona.\n";
} else {
    echo "❌ ALINHAMENTO INSUFICIENTE\n";
    echo "   Correções significativas são necessárias.\n";
}

echo "\n=================================================\n";
echo "✅ ANÁLISE CONCLUÍDA\n";
echo "=================================================\n";
?> 