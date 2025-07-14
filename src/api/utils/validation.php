<?php
function validateRequired($input, $fields) {
    foreach ($fields as $field) {
        if (empty($input[$field])) {
            return false;
        }
    }
    return true;
}

function validateAndSanitize($input, $context = null) {
    $data = [];
    
    // Validação para criação de unidade + gestor
    if ($context === 'validateUnitData') {
        $required = ['nome', 'telefone', 'cpf_gestor', 'nome_gestor', 'email_gestor', 'senha'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo obrigatório: $field");
            }
            $data[$field] = htmlspecialchars(trim($input[$field]));
        }
        // Não processa mais endereco
        // Validar confirmação de senha
        if (empty($input['confirmar_senha']) || $input['confirmar_senha'] !== $input['senha']) {
            throw new Exception("As senhas não coincidem");
        }
        return $data;
    }
    
    // Validação para criação de funcionário
    if ($context === 'validateEmployeeData') {
        $required = ['codigo_acesso', 'nome_completo', 'tipo_usuario', 'cpf', 'senha'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo obrigatório: $field");
            }
            $data[$field] = htmlspecialchars(trim($input[$field]));
        }
        
        // Validar confirmação de senha
        if (empty($input['confirmar_senha']) || $input['confirmar_senha'] !== $input['senha']) {
            throw new Exception("As senhas não coincidem");
        }
        
        return $data;
    }
    
    // Validação para criação de usuário simples
    if ($context === 'validateUserData') {
        $required = ['nome', 'cpf', 'email', 'senha'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo obrigatório: $field");
            }
            $data[$field] = htmlspecialchars(trim($input[$field]));
        }
        return $data;
    }
    
    // Default: retorna o input sem alteração
    return $input;
}
?> 