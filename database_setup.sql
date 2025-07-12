SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `easy_rake` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `easy_rake`;

-- Tabela de unidades
CREATE TABLE IF NOT EXISTS `unidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `codigo_acesso` varchar(8) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_acesso` (`codigo_acesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('gestor','caixa','sanger') NOT NULL,
  `perfil` enum('Administrador','Operador','Gerente','Gestor') DEFAULT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de associações entre usuários e unidades
CREATE TABLE IF NOT EXISTS `associacoes_usuario_unidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_unidade` int(11) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('Operador','Gerente','Gestor') NOT NULL,
  `status_aprovacao` enum('Pendente','Aprovado','Rejeitado','Removido') NOT NULL DEFAULT 'Pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_unidade` (`id_unidade`),
  CONSTRAINT `associacoes_usuario_unidade_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `associacoes_usuario_unidade_ibfk_2` FOREIGN KEY (`id_unidade`) REFERENCES `unidades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de jogadores
CREATE TABLE IF NOT EXISTS `jogadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `limite_credito` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de caixas
CREATE TABLE IF NOT EXISTS `caixas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operador_id` int(11) NOT NULL,
  `valor_inicial` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('Aberto','Fechado') NOT NULL DEFAULT 'Aberto',
  `data_abertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_fechamento` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operador_id` (`operador_id`),
  KEY `status` (`status`),
  CONSTRAINT `caixas_ibfk_1` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de movimentações
CREATE TABLE IF NOT EXISTS `movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caixa_id` int(11) NOT NULL,
  `tipo` enum('Entrada','Saida') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `operador_id` int(11) NOT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caixa_id` (`caixa_id`),
  KEY `operador_id` (`operador_id`),
  CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`),
  CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de transações de jogadores
CREATE TABLE IF NOT EXISTS `transacoes_jogadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jogador_id` int(11) NOT NULL,
  `operador_id` int(11) NOT NULL,
  `tipo` enum('CREDITO','DEBITO','ACERTO_POSITIVO','ACERTO_NEGATIVO') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `data_transacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `quitado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `jogador_id` (`jogador_id`),
  KEY `operador_id` (`operador_id`),
  CONSTRAINT `transacoes_jogadores_ibfk_1` FOREIGN KEY (`jogador_id`) REFERENCES `jogadores` (`id`),
  CONSTRAINT `transacoes_jogadores_ibfk_2` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de caixinhas
CREATE TABLE IF NOT EXISTS `caixinhas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor_meta` decimal(10,2) NOT NULL,
  `valor_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cashback_percent` decimal(5,2) DEFAULT NULL,
  `participantes` int(11) NOT NULL DEFAULT 0,
  `status` enum('Ativo','Inativo','Concluido') NOT NULL DEFAULT 'Ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_conclusao` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de inclusões em caixinhas
CREATE TABLE IF NOT EXISTS `caixinhas_inclusoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caixinha_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `operador_id` int(11) NOT NULL,
  `data_inclusao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caixinha_id` (`caixinha_id`),
  KEY `operador_id` (`operador_id`),
  CONSTRAINT `caixinhas_inclusoes_ibfk_1` FOREIGN KEY (`caixinha_id`) REFERENCES `caixinhas` (`id`),
  CONSTRAINT `caixinhas_inclusoes_ibfk_2` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de gastos
CREATE TABLE IF NOT EXISTS `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caixa_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `operador_id` int(11) NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caixa_id` (`caixa_id`),
  KEY `operador_id` (`operador_id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`),
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de aprovações
CREATE TABLE IF NOT EXISTS `aprovacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Jogador','Caixa','Relatorio') NOT NULL,
  `referencia_id` int(11) NOT NULL,
  `solicitante_id` int(11) NOT NULL,
  `aprovador_id` int(11) DEFAULT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
  `observacoes` text DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitante_id` (`solicitante_id`),
  KEY `aprovador_id` (`aprovador_id`),
  KEY `idx_aprovacoes_status` (`status`),
  KEY `idx_aprovacoes_tipo` (`tipo`),
  CONSTRAINT `aprovacoes_ibfk_1` FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `aprovacoes_ibfk_2` FOREIGN KEY (`aprovador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de relatórios
CREATE TABLE IF NOT EXISTS `relatorios_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `status` enum('Gerado','Erro') NOT NULL DEFAULT 'Gerado',
  `data_geracao` timestamp NOT NULL DEFAULT current_timestamp(),
  `arquivo` varchar(255) DEFAULT NULL,
  `mensagem_erro` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `relatorios_historico_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

ALTER TABLE caixas
ADD COLUMN inventario_real DECIMAL(10,2) NULL AFTER valor_inicial;

CREATE TABLE IF NOT EXISTS `rake` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `caixa_id` INT NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_nome` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `caixa_id` (`caixa_id`),
  CONSTRAINT `rake_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE caixinhas
ADD COLUMN caixa_id INT NOT NULL AFTER id;

ALTER TABLE caixinhas
ADD CONSTRAINT fk_caixinhas_caixa_id FOREIGN KEY (caixa_id) REFERENCES caixas(id);

ALTER TABLE caixinhas_inclusoes
ADD COLUMN usuario_id INT NOT NULL AFTER valor;

ALTER TABLE caixinhas_inclusoes
ADD CONSTRAINT fk_caixinhas_inclusoes_usuario_id FOREIGN KEY (usuario_id) REFERENCES usuarios(id);

