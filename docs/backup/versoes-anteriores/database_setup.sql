-- ==========================================================================
-- EASY RAKE - Configuração do Banco de Dados (Compatível com nova estrutura)
-- ========================================================================== 

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS easy_rake CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE easy_rake;

-- ==========================================================================
-- TABELA: usuarios
-- ==========================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('Administrador', 'Operador', 'Gerente', 'Gestor') NOT NULL DEFAULT 'Operador',
    status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================================================
-- TABELA: jogadores
-- ==========================================================================
CREATE TABLE IF NOT EXISTS jogadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(15),
    limite_credito DECIMAL(10,2) NULL DEFAULT 0.00,
    saldo_atual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    email VARCHAR(100),
    endereco TEXT,
    status ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================================================
-- TABELA: caixas
-- ==========================================================================
CREATE TABLE IF NOT EXISTS caixas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operador_id INT NOT NULL,
    valor_inicial DECIMAL(10,2) NOT NULL,
    valor_final DECIMAL(10,2) DEFAULT NULL,
    observacoes TEXT,
    status ENUM('Aberto', 'Fechado', 'Cancelado') NOT NULL DEFAULT 'Aberto',
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fechamento TIMESTAMP NULL,
    FOREIGN KEY (operador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ==========================================================================
-- TABELA: aprovacoes
-- ==========================================================================
CREATE TABLE IF NOT EXISTS aprovacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Jogador', 'Caixa', 'Relatorio') NOT NULL,
    referencia_id INT NOT NULL,
    solicitante_id INT NOT NULL,
    aprovador_id INT NULL,
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') NOT NULL DEFAULT 'Pendente',
    observacoes TEXT,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ==========================================================================
-- TABELA: movimentacoes
-- ==========================================================================
CREATE TABLE IF NOT EXISTS movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caixa_id INT NOT NULL,
    tipo ENUM('Entrada', 'Saida') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    operador_id INT NOT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caixa_id) REFERENCES caixas(id) ON DELETE CASCADE,
    FOREIGN KEY (operador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ==========================================================================
-- TABELA: unidades
-- ==========================================================================
CREATE TABLE IF NOT EXISTS unidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    codigo_acesso VARCHAR(50) UNIQUE,
    status ENUM('Ativa', 'Inativa') DEFAULT 'Ativa',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================================
-- TABELA: associacoes_usuario_unidade
-- ==========================================================================
CREATE TABLE IF NOT EXISTS associacoes_usuario_unidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_unidade INT NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('Operador', 'Gerente', 'Gestor') NOT NULL DEFAULT 'Operador',
    status_aprovacao ENUM('Pendente', 'Aprovado', 'Rejeitado') NOT NULL DEFAULT 'Pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_unidade) REFERENCES unidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_unidade (id_usuario, id_unidade)
);

-- ==========================================================================
-- TABELA: transacoes_jogadores
-- ==========================================================================
CREATE TABLE IF NOT EXISTS transacoes_jogadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogador_id INT NOT NULL,
    operador_id INT NOT NULL,
    tipo ENUM('COMPRA', 'DEVOLUCAO', 'ACERTO_POSITIVO', 'ACERTO_NEGATIVO', 'TRANSFERENCIA_ENTRADA', 'TRANSFERENCIA_SAIDA') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    observacao VARCHAR(255),
    quitado BOOLEAN NOT NULL DEFAULT FALSE,
    data_transacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogador_id) REFERENCES jogadores(id) ON DELETE CASCADE,
    FOREIGN KEY (operador_id) REFERENCES usuarios(id)
);

-- ==========================================================================
-- INSERIR DADOS INICIAIS
-- ==========================================================================

-- Usuário administrador padrão (senha: password)
INSERT INTO usuarios (nome, cpf, email, senha, perfil, status) VALUES 
('Administrador', '000.000.000-00', 'admin@easyrake.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Ativo');

-- Jogadores de exemplo
INSERT INTO jogadores (nome, cpf, telefone, limite_credito, saldo_atual, email, status) VALUES 
('João Silva', '123.456.789-00', '(11) 99999-9999', 0.00, 0.00, 'joao@email.com', 'Ativo'),
('Maria Santos', '987.654.321-00', '(11) 88888-8888', 0.00, 0.00, 'maria@email.com', 'Ativo'),
('Pedro Oliveira', '456.789.123-00', '(11) 77777-7777', 0.00, 0.00, 'pedro@email.com', 'Ativo');

-- Caixas de exemplo
INSERT INTO caixas (operador_id, valor_inicial, observacoes, status) VALUES 
(1, 1000.00, 'Caixa inicial do dia', 'Aberto'),
(1, 500.00, 'Caixa reserva', 'Fechado');

-- Aprovações de exemplo
INSERT INTO aprovacoes (tipo, referencia_id, solicitante_id, status, observacoes) VALUES 
('Jogador', 1, 1, 'Pendente', 'Aprovação de novo jogador'),
('Caixa', 1, 1, 'Aprovado', 'Aprovação de abertura de caixa');

-- ==========================================================================
-- ÍNDICES PARA MELHOR PERFORMANCE
-- ==========================================================================

CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_status ON usuarios(status);

CREATE INDEX idx_jogadores_cpf ON jogadores(cpf);
CREATE INDEX idx_jogadores_status ON jogadores(status);
CREATE INDEX idx_jogadores_data_cadastro ON jogadores(data_cadastro);

CREATE INDEX idx_caixas_operador ON caixas(operador_id);
CREATE INDEX idx_caixas_status ON caixas(status);
CREATE INDEX idx_caixas_data_abertura ON caixas(data_abertura);

CREATE INDEX idx_aprovacoes_status ON aprovacoes(status);
CREATE INDEX idx_aprovacoes_tipo ON aprovacoes(tipo);
CREATE INDEX idx_aprovacoes_data_solicitacao ON aprovacoes(data_solicitacao);

CREATE INDEX idx_movimentacoes_caixa ON movimentacoes(caixa_id);
CREATE INDEX idx_movimentacoes_data ON movimentacoes(data_movimentacao);

CREATE INDEX idx_unidades_codigo_acesso ON unidades(codigo_acesso);
CREATE INDEX idx_unidades_status ON unidades(status);

CREATE INDEX idx_associacoes_usuario ON associacoes_usuario_unidade(id_usuario);
CREATE INDEX idx_associacoes_unidade ON associacoes_usuario_unidade(id_unidade);
CREATE INDEX idx_associacoes_status ON associacoes_usuario_unidade(status_aprovacao);

-- ==========================================================================
-- ATUALIZAÇÕES PARA BANCOS EXISTENTES
-- ==========================================================================

-- Adicionar perfil 'Gestor' se não existir
ALTER TABLE usuarios MODIFY COLUMN perfil ENUM('Administrador', 'Operador', 'Gerente', 'Gestor') NOT NULL DEFAULT 'Operador';

-- Criar tabela de associações se não existir
CREATE TABLE IF NOT EXISTS associacoes_usuario_unidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_unidade INT NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('Operador', 'Gerente', 'Gestor') NOT NULL DEFAULT 'Operador',
    status_aprovacao ENUM('Pendente', 'Aprovado', 'Rejeitado') NOT NULL DEFAULT 'Pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_unidade) REFERENCES unidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_unidade (id_usuario, id_unidade)
);

-- Adicionar índices se não existirem
CREATE INDEX IF NOT EXISTS idx_unidades_codigo_acesso ON unidades(codigo_acesso);
CREATE INDEX IF NOT EXISTS idx_unidades_status ON unidades(status);
CREATE INDEX IF NOT EXISTS idx_associacoes_usuario ON associacoes_usuario_unidade(id_usuario);
CREATE INDEX IF NOT EXISTS idx_associacoes_unidade ON associacoes_usuario_unidade(id_unidade);
CREATE INDEX IF NOT EXISTS idx_associacoes_status ON associacoes_usuario_unidade(status_aprovacao);

ALTER TABLE usuarios MODIFY COLUMN email VARCHAR(100) NULL;
ALTER TABLE usuarios DROP INDEX email;