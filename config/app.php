<?php
/**
 * Configuração Geral da Aplicação - Easy Rake
 * 
 * Este arquivo contém configurações gerais da aplicação.
 */

return [
    'name' => 'Easy Rake',
    'version' => '2.0',
    'environment' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') ?: true,
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    
    // Configurações de segurança
    'security' => [
        'session_lifetime' => 3600, // 1 hora
        'password_min_length' => 6,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
    ],
    
    // Configurações de upload
    'upload' => [
        'max_file_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'upload_path' => __DIR__ . '/../public/uploads/',
    ],
    
    // Configurações de log
    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'path' => __DIR__ . '/../logs/',
    ],
];
?> 