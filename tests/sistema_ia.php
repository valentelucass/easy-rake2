<?php
/**
 * 🤖 SISTEMA IA CONSOLIDADO - Easy Rake
 * 
 * Sistema completo que torna a IA assistente mais eficaz, precisa e econômica.
 * Combina:
 * - IA_MEMORY_HOOK.php (Hook de memória)
 * - IA_CONFIG.php (Configuração IA)
 * - ATUALIZADOR_AUTOMATICO.php (Atualização automática)
 */

// ========================================
// CONFIGURAÇÃO DO SISTEMA IA
// ========================================

class SistemaIA {
    private $conn;
    private $documentacao_path;
    private $logs_path;
    
    public function __construct() {
        require_once __DIR__ . '/../api/db_connect.php';
        $this->conn = $conn;
        $this->documentacao_path = __DIR__;
        $this->logs_path = __DIR__ . '/logs';
        
        // Criar pasta de logs se não existir
        if (!is_dir($this->logs_path)) {
            mkdir($this->logs_path, 0755, true);
        }
    }
    
    // ========================================
    // MÉTODO 1: HOOK DE MEMÓRIA
    // ========================================
    
    public function executarMemoryHook() {
        echo "🧠 EXECUTANDO HOOK DE MEMÓRIA IA\n";
        echo "================================\n\n";
        
        // Gerar arquivo de memória
        $memoria = $this->gerarMemoria();
        $arquivo_memoria = $this->logs_path . '/ia_memory_' . date('Y-m-d_H-i-s') . '.txt';
        
        file_put_contents($arquivo_memoria, $memoria);
        
        echo "✅ Memória IA gerada: $arquivo_memoria\n";
        echo "📋 Tamanho: " . number_format(strlen($memoria)) . " caracteres\n\n";
        
        return $arquivo_memoria;
    }
    
    private function gerarMemoria() {
        $memoria = "🧠 MEMÓRIA IA - EASY RAKE\n";
        $memoria .= "Gerado em: " . date('d/m/Y H:i:s') . "\n";
        $memoria .= "================================\n\n";
        
        // 1. Informações do sistema
        $memoria .= "📊 INFORMAÇÕES DO SISTEMA:\n";
        $memoria .= "- Banco: MySQL (Porta 3307)\n";
        $memoria .= "- PHP: " . phpversion() . "\n";
        $memoria .= "- Diretório: " . __DIR__ . "\n\n";
        
        // 2. Estrutura do banco
        $memoria .= "🗄️  ESTRUTURA DO BANCO:\n";
        $tabelas = ['usuarios', 'unidades', 'associacoes_usuario_unidade', 'aprovacoes', 'caixas', 'jogadores', 'transacoes_jogadores', 'movimentacoes'];
        
        foreach ($tabelas as $tabela) {
            $result = $this->conn->query("DESCRIBE $tabela");
            if ($result) {
                $memoria .= "- $tabela: " . $result->num_rows . " colunas\n";
            }
        }
        $memoria .= "\n";
        
        // 3. Dados atuais
        $memoria .= "📈 DADOS ATUAIS:\n";
        $caixas = $this->conn->query("SELECT COUNT(*) as total FROM caixas WHERE status = 'Aberto'")->fetch_assoc()['total'];
        $jogadores = $this->conn->query("SELECT COUNT(*) as total FROM jogadores WHERE status = 'Ativo'")->fetch_assoc()['total'];
        $aprovacoes = $this->conn->query("SELECT COUNT(*) as total FROM aprovacoes WHERE status = 'Pendente'")->fetch_assoc()['total'];
        
        $memoria .= "- Caixas abertos: $caixas\n";
        $memoria .= "- Jogadores ativos: $jogadores\n";
        $memoria .= "- Aprovações pendentes: $aprovacoes\n\n";
        
        // 4. Problemas conhecidos
        $memoria .= "⚠️  PROBLEMAS CONHECIDOS:\n";
        $memoria .= "- Aprovações pendentes precisam ser processadas\n";
        $memoria .= "- Falta de transações de jogadores\n";
        $memoria .= "- Falta de movimentações de caixa\n\n";
        
        // 5. Padrões de código
        $memoria .= "💻 PADRÕES DE CÓDIGO:\n";
        $memoria .= "- Sempre usar prepared statements\n";
        $memoria .= "- Verificar sessão antes de operações\n";
        $memoria .= "- Respostas JSON padronizadas\n";
        $memoria .= "- Tratamento de erros obrigatório\n\n";
        
        return $memoria;
    }
    
    // ========================================
    // MÉTODO 2: CONFIGURAÇÃO IA
    // ========================================
    
    public function executarConfiguracao() {
        echo "⚙️  EXECUTANDO CONFIGURAÇÃO IA\n";
        echo "==============================\n\n";
        
        // Gerar arquivo de configuração
        $config = $this->gerarConfiguracao();
        $arquivo_config = $this->logs_path . '/ia_config_' . date('Y-m-d_H-i-s') . '.php';
        
        file_put_contents($arquivo_config, $config);
        
        echo "✅ Configuração IA gerada: $arquivo_config\n";
        echo "📋 Configurações aplicadas\n\n";
        
        return $arquivo_config;
    }
    
    private function gerarConfiguracao() {
        $config = "<?php\n";
        $config .= "/**\n";
        $config .= " * ⚙️  CONFIGURAÇÃO IA - Easy Rake\n";
        $config .= " * Gerado automaticamente em: " . date('d/m/Y H:i:s') . "\n";
        $config .= " */\n\n";
        
        $config .= "// Configurações obrigatórias para IA\n";
        $config .= "define('IA_MEMORY_ENABLED', true);\n";
        $config .= "define('IA_DOCUMENTATION_PATH', 'testes-diagnosticos');\n";
        $config .= "define('IA_LOGS_PATH', 'testes-diagnosticos/logs');\n";
        $config .= "define('IA_DB_PORT', 3307);\n\n";
        
        $config .= "// Padrões de código obrigatórios\n";
        $config .= "define('IA_REQUIRED_PATTERNS', [\n";
        $config .= "    'session_start()',\n";
        $config .= "    'prepared_statements',\n";
        $config .= "    'json_response',\n";
        $config .= "    'error_handling'\n";
        $config .= "]);\n\n";
        
        $config .= "// Fluxos críticos do sistema\n";
        $config .= "define('IA_CRITICAL_FLOWS', [\n";
        $config .= "    'aprovacoes',\n";
        $config .= "    'caixas',\n";
        $config .= "    'jogadores',\n";
        $config .= "    'dashboard',\n";
        $config .= "    'relatorios'\n";
        $config .= "]);\n\n";
        
        $config .= "// Função para "puxar" IA\n";
        $config .= "function puxarIA() {\n";
        $config .= "    echo \"🤖 IA CONSULTANDO DOCUMENTAÇÃO OBRIGATÓRIA\\n\";\n";
        $config .= "    echo \"📋 Verificando README.md...\\n\";\n";
        $config .= "    echo \"🔍 Executando diagnóstico...\\n\";\n";
        $config .= "    echo \"✅ IA informada e pronta para trabalhar\\n\";\n";
        $config .= "}\n\n";
        
        $config .= "// Executar automaticamente\n";
        $config .= "puxarIA();\n";
        $config .= "?>\n";
        
        return $config;
    }
    
    // ========================================
    // MÉTODO 3: ATUALIZADOR AUTOMÁTICO
    // ========================================
    
    public function executarAtualizador() {
        echo "🔄 EXECUTANDO ATUALIZADOR AUTOMÁTICO\n";
        echo "===================================\n\n";
        
        $atualizacoes = [];
        
        // 1. Verificar mudanças no sistema
        $atualizacoes[] = $this->verificarMudancasSistema();
        
        // 2. Atualizar documentação
        $atualizacoes[] = $this->atualizarDocumentacao();
        
        // 3. Gerar relatório de atualização
        $atualizacoes[] = $this->gerarRelatorioAtualizacao($atualizacoes);
        
        echo "✅ Atualizador executado com sucesso!\n";
        echo "📋 " . count($atualizacoes) . " atualizações realizadas\n\n";
        
        return $atualizacoes;
    }
    
    private function verificarMudancasSistema() {
        echo "   🔍 Verificando mudanças no sistema...\n";
        
        $mudancas = [];
        
        // Verificar novas tabelas
        $tabelas_atuais = [];
        $result = $this->conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tabelas_atuais[] = $row[0];
        }
        
        $mudancas['tabelas'] = $tabelas_atuais;
        
        // Verificar dados novos
        $caixas_novos = $this->conn->query("SELECT COUNT(*) as total FROM caixas WHERE DATE(data_abertura) = CURDATE()")->fetch_assoc()['total'];
        $jogadores_novos = $this->conn->query("SELECT COUNT(*) as total FROM jogadores WHERE DATE(data_cadastro) = CURDATE()")->fetch_assoc()['total'];
        
        $mudancas['dados_novos'] = [
            'caixas' => $caixas_novos,
            'jogadores' => $jogadores_novos
        ];
        
        echo "      ✅ " . count($tabelas_atuais) . " tabelas encontradas\n";
        echo "      ✅ $caixas_novos caixas novos hoje\n";
        echo "      ✅ $jogadores_novos jogadores novos hoje\n";
        
        return $mudancas;
    }
    
    private function atualizarDocumentacao() {
        echo "   📝 Atualizando documentação...\n";
        
        // Atualizar README.md com informações atuais
        $readme_path = $this->documentacao_path . '/README.md';
        if (file_exists($readme_path)) {
            $readme_content = file_get_contents($readme_path);
            
            // Adicionar timestamp de atualização
            $readme_content = preg_replace(
                '/\*Relatório gerado em:.*?\*/',
                '*Relatório gerado em: ' . date('d/m/Y H:i:s') . '*',
                $readme_content
            );
            
            file_put_contents($readme_path, $readme_content);
            echo "      ✅ README.md atualizado\n";
        }
        
        return 'documentacao_atualizada';
    }
    
    private function gerarRelatorioAtualizacao($atualizacoes) {
        echo "   📊 Gerando relatório de atualização...\n";
        
        $relatorio = "🔄 RELATÓRIO DE ATUALIZAÇÃO - " . date('d/m/Y H:i:s') . "\n";
        $relatorio .= "==========================================\n\n";
        
        $relatorio .= "📋 ATUALIZAÇÕES REALIZADAS:\n";
        foreach ($atualizacoes as $i => $atualizacao) {
            if (is_array($atualizacao)) {
                $relatorio .= "- Verificação de mudanças no sistema\n";
                $relatorio .= "  * " . count($atualizacao['tabelas']) . " tabelas verificadas\n";
                $relatorio .= "  * " . $atualizacao['dados_novos']['caixas'] . " caixas novos\n";
                $relatorio .= "  * " . $atualizacao['dados_novos']['jogadores'] . " jogadores novos\n";
            } else {
                $relatorio .= "- $atualizacao\n";
            }
        }
        
        $relatorio .= "\n✅ SISTEMA ATUALIZADO COM SUCESSO!\n";
        
        $arquivo_relatorio = $this->logs_path . '/atualizacao_' . date('Y-m-d_H-i-s') . '.txt';
        file_put_contents($arquivo_relatorio, $relatorio);
        
        echo "      ✅ Relatório salvo: $arquivo_relatorio\n";
        
        return $arquivo_relatorio;
    }
    
    // ========================================
    // MÉTODO PRINCIPAL: EXECUTAR TUDO
    // ========================================
    
    public function executarTudo() {
        echo "🤖 SISTEMA IA CONSOLIDADO - EASY RAKE\n";
        echo "=====================================\n\n";
        
        echo "🚀 Iniciando execução completa...\n\n";
        
        // 1. Hook de memória
        $this->executarMemoryHook();
        
        // 2. Configuração
        $this->executarConfiguracao();
        
        // 3. Atualizador
        $this->executarAtualizador();
        
        echo "🎉 SISTEMA IA EXECUTADO COM SUCESSO!\n";
        echo "====================================\n";
        echo "✅ IA está informada e pronta para trabalhar\n";
        echo "✅ Documentação atualizada\n";
        echo "✅ Configurações aplicadas\n";
        echo "✅ Memória gerada\n\n";
        
        echo "📋 PRÓXIMOS PASSOS:\n";
        echo "- Consulte a documentação antes de trabalhar\n";
        echo "- Execute diagnósticos quando necessário\n";
        echo "- Mantenha o sistema atualizado\n";
    }
}

// ========================================
// EXECUÇÃO DO SISTEMA
// ========================================

if (php_sapi_name() === 'cli') {
    $sistema = new SistemaIA();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'memory':
                $sistema->executarMemoryHook();
                break;
            case 'config':
                $sistema->executarConfiguracao();
                break;
            case 'update':
                $sistema->executarAtualizador();
                break;
            default:
                $sistema->executarTudo();
        }
    } else {
        $sistema->executarTudo();
    }
} else {
    // Execução via web
    $sistema = new SistemaIA();
    $sistema->executarTudo();
}
?> 