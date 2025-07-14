<?php
/**
 * Funções auxiliares para o sistema Easy Rake
 */

require_once __DIR__ . '/../db_connect.php';

/**
 * Obtém informações completas do funcionário logado
 * @return array|null Array com dados do funcionário ou null se não encontrado
 */
function getCurrentFuncionario() {
    $funcionario_id = getCurrentFuncionarioId();
    if (!$funcionario_id) {
        return null;
    }
    
    $conn = getConnection();
    if (!$conn) {
        return null;
    }
    
    try {
        $sql = "SELECT f.*, u.nome as usuario_nome, u.email, un.nome as unidade_nome 
                FROM funcionarios f 
                JOIN usuarios u ON f.usuario_id = u.id 
                JOIN unidades un ON f.unidade_id = un.id 
                WHERE f.id = ? AND f.status = 'Ativo'";
        
        $stmt = executePreparedQuery($conn, $sql, "i", [$funcionario_id]);
        
        if ($stmt === false) {
            return null;
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row;
        
    } catch (Exception $e) {
        error_log("Erro ao obter dados do funcionário: " . $e->getMessage());
        return null;
    } finally {
        closeConnection($conn);
    }
}
?> 