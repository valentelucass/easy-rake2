<?php
/**
 * 🔧 CORRETOR AUTOMÁTICO - Easy Rake
 * 
 * Script que corrige automaticamente problemas comuns detectados
 * pelo diagnóstico inteligente. Use com cuidado e sempre faça backup.
 * 
 * SEMPRE execute o diagnóstico inteligente antes de usar este corretor.
 */

require_once __DIR__ . '/../src/api/db_connect.php';

echo "🔧 CORRETOR AUTOMÁTICO - EASY RAKE\n";
echo "==================================\n\n";

class CorretorAutomatico {
    private $conn;
    private $correcoes_realizadas = [];
    private $erros = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function corrigirProblemasComuns() {
        echo "🔍 INICIANDO CORREÇÕES AUTOMÁTICAS...\n\n";
        
        $this->corrigirAprovacoesOrfas();
        $this->corrigirUsuariosSemAssociacao();
        $this->corrigirSenhasInseguras();
        $this->corrigirIntegridadeReferencial();
        $this->limparDadosInconsistentes();
        $this->gerarRelatorioCorrecoes();
    }
    
    private function corrigirAprovacoesOrfas() {
        echo "1. 🔧 CORRIGINDO APROVAÇÕES ÓRFÃS...\n";
        
        // Buscar aprovações órfãs
        $sql = "SELECT a.id, a.tipo, a.referencia_id FROM aprovacoes a 
                LEFT JOIN caixas c ON a.referencia_id = c.id AND a.tipo = 'Caixa'
                LEFT JOIN jogadores j ON a.referencia_id = j.id AND a.tipo = 'Jogador'
                WHERE a.status = 'Pendente' AND c.id IS NULL AND j.id IS NULL";
        $result = $this->conn->query($sql);
        
        $aprovacoes_orfas = $result->num_rows;
        if ($aprovacoes_orfas > 0) {
            echo "   📋 Encontradas $aprovacoes_orfas aprovações órfãs\n";
            
            // Marcar como rejeitadas com observação
            $sql_update = "UPDATE aprovacoes SET 
                          status = 'Rejeitado', 
                          observacoes = CONCAT(COALESCE(observacoes, ''), ' [AUTO-REJEITADA: Referência inválida]'),
                          data_aprovacao = NOW()
                          WHERE id IN (
                              SELECT a.id FROM aprovacoes a 
                              LEFT JOIN caixas c ON a.referencia_id = c.id AND a.tipo = 'Caixa'
                              LEFT JOIN jogadores j ON a.referencia_id = j.id AND a.tipo = 'Jogador'
                              WHERE a.status = 'Pendente' AND c.id IS NULL AND j.id IS NULL
                          )";
            
            if ($this->conn->query($sql_update)) {
                echo "   ✅ Aprovações órfãs marcadas como rejeitadas\n";
                $this->correcoes_realizadas[] = "Aprovações órfãs corrigidas: $aprovacoes_orfas";
            } else {
                echo "   ❌ Erro ao corrigir aprovações órfãs\n";
                $this->erros[] = "Falha ao corrigir aprovações órfãs: " . $this->conn->error;
            }
        } else {
            echo "   ✅ Nenhuma aprovação órfã encontrada\n";
        }
    }
    
    private function corrigirUsuariosSemAssociacao() {
        echo "\n2. 🔧 CORRIGINDO USUÁRIOS SEM ASSOCIAÇÃO...\n";
        
        // Buscar usuários sem associação
        $sql = "SELECT u.id, u.nome, u.tipo_usuario FROM usuarios u 
                LEFT JOIN associacoes_usuario_unidade aau ON u.id = aau.id_usuario 
                WHERE aau.id IS NULL";
        $result = $this->conn->query($sql);
        
        $usuarios_sem_assoc = $result->num_rows;
        if ($usuarios_sem_assoc > 0) {
            echo "   👤 Encontrados $usuarios_sem_assoc usuários sem associação\n";
            
            // Buscar primeira unidade disponível
            $sql_unidade = "SELECT id FROM unidades WHERE status = 'Ativa' LIMIT 1";
            $result_unidade = $this->conn->query($sql_unidade);
            
            if ($result_unidade->num_rows > 0) {
                $unidade = $result_unidade->fetch_assoc();
                $unidade_id = $unidade['id'];
                
                // Criar associações pendentes
                $stmt = $this->conn->prepare("INSERT INTO associacoes_usuario_unidade 
                                            (id_usuario, id_unidade, senha_hash, perfil, status_aprovacao, data_criacao) 
                                            VALUES (?, ?, ?, ?, 'Pendente', NOW())");
                
                $corrigidos = 0;
                while ($usuario = $result->fetch_assoc()) {
                    $senha_hash = password_hash('senha123', PASSWORD_DEFAULT);
                    $perfil = $usuario['tipo_usuario'] == 'gestor' ? 'Gestor' : 'Operador';
                    
                    $stmt->bind_param("iiss", $usuario['id'], $unidade_id, $senha_hash, $perfil);
                    if ($stmt->execute()) {
                        $corrigidos++;
                    }
                }
                $stmt->close();
                
                echo "   ✅ $corrigidos usuários associados à unidade $unidade_id (pendentes)\n";
                $this->correcoes_realizadas[] = "Usuários sem associação corrigidos: $corrigidos";
            } else {
                echo "   ❌ Nenhuma unidade ativa encontrada para associação\n";
                $this->erros[] = "Não foi possível associar usuários: nenhuma unidade ativa";
            }
        } else {
            echo "   ✅ Todos os usuários têm associação\n";
        }
    }
    
    private function corrigirSenhasInseguras() {
        echo "\n3. 🔧 CORRIGINDO SENHAS INSEGURAS...\n";
        
        // Buscar senhas não hasheadas
        $sql = "SELECT id, senha FROM usuarios 
                WHERE LENGTH(senha) < 60 OR senha NOT LIKE '$2y$%'";
        $result = $this->conn->query($sql);
        
        $senhas_inseguras = $result->num_rows;
        if ($senhas_inseguras > 0) {
            echo "   🔒 Encontradas $senhas_inseguras senhas inseguras\n";
            
            $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $corrigidas = 0;
            
            while ($usuario = $result->fetch_assoc()) {
                // Hash da senha original ou senha padrão
                $senha_original = $usuario['senha'];
                $senha_hash = password_hash($senha_original, PASSWORD_DEFAULT);
                
                $stmt->bind_param("si", $senha_hash, $usuario['id']);
                if ($stmt->execute()) {
                    $corrigidas++;
                }
            }
            $stmt->close();
            
            echo "   ✅ $corrigidas senhas corrigidas com hash seguro\n";
            $this->correcoes_realizadas[] = "Senhas inseguras corrigidas: $corrigidas";
        } else {
            echo "   ✅ Todas as senhas estão seguras\n";
        }
    }
    
    private function corrigirIntegridadeReferencial() {
        echo "\n4. 🔧 CORRIGINDO INTEGRIDADE REFERENCIAL...\n";
        
        // Verificar caixas sem operador válido
        $sql = "SELECT COUNT(*) as total FROM caixas c 
                LEFT JOIN associacoes_usuario_unidade aau ON c.operador_id = aau.id_usuario 
                WHERE aau.id IS NULL";
        $result = $this->conn->query($sql);
        $caixas_sem_operador = $result->fetch_assoc()['total'];
        
        if ($caixas_sem_operador > 0) {
            echo "   📦 Encontrados $caixas_sem_operador caixas sem operador válido\n";
            
            // Marcar como fechados
            $sql_update = "UPDATE caixas SET status = 'Fechado', data_fechamento = NOW() 
                          WHERE operador_id NOT IN (
                              SELECT id_usuario FROM associacoes_usuario_unidade 
                              WHERE status_aprovacao = 'Aprovado'
                          )";
            
            if ($this->conn->query($sql_update)) {
                echo "   ✅ Caixas sem operador válido marcados como fechados\n";
                $this->correcoes_realizadas[] = "Caixas sem operador corrigidos: $caixas_sem_operador";
            }
        } else {
            echo "   ✅ Todos os caixas têm operador válido\n";
        }
    }
    
    private function limparDadosInconsistentes() {
        echo "\n5. 🔧 LIMPANDO DADOS INCONSISTENTES...\n";
        
        // Limpar sessões antigas (simulação)
        $sql = "DELETE FROM aprovacoes WHERE 
                status = 'Pendente' AND 
                data_solicitacao < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $result = $this->conn->query($sql);
        $aprovacoes_limpas = $this->conn->affected_rows;
        
        if ($aprovacoes_limpas > 0) {
            echo "   🗑️  $aprovacoes_limpas aprovações antigas removidas\n";
            $this->correcoes_realizadas[] = "Aprovações antigas removidas: $aprovacoes_limpas";
        } else {
            echo "   ✅ Nenhuma aprovação antiga encontrada\n";
        }
        
        // Atualizar estatísticas
        $this->conn->query("ANALYZE TABLE usuarios, aprovacoes, caixas");
        echo "   📊 Estatísticas das tabelas atualizadas\n";
    }
    
    private function gerarRelatorioCorrecoes() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📋 RELATÓRIO DE CORREÇÕES AUTOMÁTICAS\n";
        echo str_repeat("=", 50) . "\n\n";
        
        if (count($this->correcoes_realizadas) > 0) {
            echo "✅ CORREÇÕES REALIZADAS:\n";
            foreach ($this->correcoes_realizadas as $correcao) {
                echo "   • $correcao\n";
            }
            echo "\n";
        } else {
            echo "✅ NENHUMA CORREÇÃO NECESSÁRIA\n\n";
        }
        
        if (count($this->erros) > 0) {
            echo "❌ ERROS ENCONTRADOS:\n";
            foreach ($this->erros as $erro) {
                echo "   • $erro\n";
            }
            echo "\n";
        }
        
        echo "🔗 PRÓXIMOS PASSOS:\n";
        echo "1. Execute o diagnóstico inteligente novamente\n";
        echo "2. Verifique se os problemas foram resolvidos\n";
        echo "3. Teste as funcionalidades do sistema\n";
        echo "4. Faça backup dos dados se necessário\n\n";
        
        echo "⚠️  AVISOS IMPORTANTES:\n";
        echo "• Sempre faça backup antes de usar correções automáticas\n";
        echo "• Revise as correções realizadas\n";
        echo "• Teste o sistema após as correções\n";
        echo "• Monitore logs de erro\n\n";
        
        echo str_repeat("=", 50) . "\n";
        echo "✅ CORREÇÕES CONCLUÍDAS\n";
        echo str_repeat("=", 50) . "\n";
    }
}

// Executar correções
echo "⚠️  AVISO: Este script fará alterações no banco de dados.\n";
echo "Certifique-se de ter feito backup antes de continuar.\n\n";

echo "Deseja continuar? (digite 'SIM' para confirmar): ";
$handle = fopen("php://stdin", "r");
$confirmacao = trim(fgets($handle));
fclose($handle);

if (strtoupper($confirmacao) === 'SIM') {
    // Criar conexão com o banco
    $conn = getConnection();
    if (!$conn) {
        echo "Erro ao conectar ao banco de dados.\n";
        exit(1);
    }

    // Remover usuário de testes (CPF 12924554466)
    echo "Removendo usuário de testes (CPF 12924554466)...\n";
    $conn->query("DELETE FROM usuarios WHERE cpf = '12924554466'");

    try {
        $corretor = new CorretorAutomatico($conn);
        $corretor->corrigirProblemasComuns();
    } catch (Exception $e) {
        echo "❌ ERRO NO CORRETOR: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Operação cancelada pelo usuário.\n";
}

$conn->close();
?> 