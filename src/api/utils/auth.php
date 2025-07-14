<?php
// Evitar múltiplas chamadas de session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function hasPermission($perm) {
    // Exemplo: permissões podem ser armazenadas na sessão
    if (!isset($_SESSION['permissions'])) return false;
    return in_array($perm, $_SESSION['permissions']);
}

function canManageUnits() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Gestor';
}

function canManageUsers() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Gestor';
}

function canApprove() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Gestor';
}

function getCurrentFuncionarioId() {
    return $_SESSION['funcionario_id'] ?? null;
}

function getCurrentUnidadeId() {
    return $_SESSION['unidade_id'] ?? null;
}

function isFirstUser() {
    // Verificar se é o primeiro usuário do sistema
    require_once __DIR__ . '/../db_connect.php';
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $row['total'] == 0;
}
?> 