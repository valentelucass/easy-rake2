-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 14/07/2025 às 03:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `easy_rake`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aprovacoes`
--

CREATE TABLE `aprovacoes` (
  `id` int(11) NOT NULL,
  `tipo` enum('Gasto','Inclusao_Caixinha','Movimentacao_Alta','Jogador_Limite_Alto') NOT NULL,
  `referencia_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_decisao` timestamp NULL DEFAULT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aprovacoes_acesso`
--

CREATE TABLE `aprovacoes_acesso` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `tipo` enum('Sanger','Caixa') NOT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_decisao` timestamp NULL DEFAULT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `aprovacoes_acesso`
--

INSERT INTO `aprovacoes_acesso` (`id`, `funcionario_id`, `tipo`, `status`, `data_solicitacao`, `data_decisao`, `gestor_id`, `observacoes`) VALUES
(2, 8, 'Caixa', 'Pendente', '2025-07-14 00:23:19', NULL, NULL, NULL),
(3, 9, 'Sanger', 'Pendente', '2025-07-14 00:23:19', NULL, NULL, NULL),
(4, 10, 'Caixa', 'Pendente', '2025-07-14 00:23:19', NULL, NULL, NULL),
(5, 11, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL, NULL),
(6, 12, 'Caixa', 'Pendente', '2025-07-14 00:23:20', NULL, NULL, NULL),
(7, 13, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL, NULL),
(8, 14, 'Caixa', 'Pendente', '2025-07-14 00:23:20', NULL, NULL, NULL),
(9, 15, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aprovacoes_acesso_historico`
--

CREATE TABLE `aprovacoes_acesso_historico` (
  `id` int(11) NOT NULL,
  `aprovacao_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `data_acao` datetime NOT NULL,
  `funcionario_aprovador_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixas`
--

CREATE TABLE `caixas` (
  `id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `funcionario_abertura_id` int(11) NOT NULL,
  `funcionario_fechamento_id` int(11) DEFAULT NULL,
  `status` enum('Aberto','Fechado','Cancelado') DEFAULT 'Aberto',
  `data_abertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_fechamento` timestamp NULL DEFAULT NULL,
  `inventario_inicial` decimal(10,2) NOT NULL,
  `inventario_final` decimal(10,2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixinhas`
--

CREATE TABLE `caixinhas` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `funcionario_criacao_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `valor_meta` decimal(10,2) DEFAULT 0.00,
  `valor_atual` decimal(10,2) DEFAULT 0.00,
  `status` enum('Ativo','Inativo','Concluido') DEFAULT 'Ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_conclusao` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixinhas_inclusoes`
--

CREATE TABLE `caixinhas_inclusoes` (
  `id` int(11) NOT NULL,
  `caixinha_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_inclusao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fichas_denom`
--

CREATE TABLE `fichas_denom` (
  `id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(20) DEFAULT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `cargo` enum('Gestor','Caixa','Sanger') NOT NULL,
  `status` enum('Pendente','Ativo','Inativo','Rejeitado') DEFAULT 'Pendente',
  `data_vinculo` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  `data_demissao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `usuario_id`, `unidade_id`, `cargo`, `status`, `data_vinculo`, `data_aprovacao`, `data_demissao`) VALUES
(7, 12, 6, 'Gestor', 'Ativo', '2025-07-14 00:21:43', '2025-07-14 00:21:43', NULL),
(8, 13, 6, 'Caixa', 'Pendente', '2025-07-14 00:23:19', NULL, NULL),
(9, 14, 6, 'Sanger', 'Pendente', '2025-07-14 00:23:19', NULL, NULL),
(10, 15, 6, 'Caixa', 'Pendente', '2025-07-14 00:23:19', NULL, NULL),
(11, 16, 6, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL),
(12, 17, 6, 'Caixa', 'Pendente', '2025-07-14 00:23:20', NULL, NULL),
(13, 18, 6, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL),
(14, 19, 6, 'Caixa', 'Pendente', '2025-07-14 00:23:20', NULL, NULL),
(15, 20, 6, 'Sanger', 'Pendente', '2025-07-14 00:23:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `categoria` enum('Alimentacao','Limpeza','Manutencao','Outros') DEFAULT 'Outros',
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Registrado','Aprovado','Rejeitado') DEFAULT 'Registrado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inventario_fichas`
--

CREATE TABLE `inventario_fichas` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `ficha_denom_id` int(11) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL DEFAULT 0,
  `quantidade_final` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogadores`
--

CREATE TABLE `jogadores` (
  `id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `saldo_atual` decimal(10,2) DEFAULT 0.00,
  `funcionario_cadastro_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_fichas`
--

CREATE TABLE `movimentacoes_fichas` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `jogador_id` int(11) DEFAULT NULL,
  `funcionario_id` int(11) NOT NULL,
  `tipo` enum('COMPRA','DEVOLUCAO','RAKE','CAIXINHA','TRANSFERENCIA') NOT NULL,
  `ficha_denom_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `rake`
--

CREATE TABLE `rake` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorios_historico`
--

CREATE TABLE `relatorios_historico` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `tipo` enum('Caixa','Gastos','Movimentacoes','Jogadores','Rake','Caixinhas') NOT NULL,
  `status` enum('Gerado','Erro','Processando') DEFAULT 'Processando',
  `data_geracao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_conclusao` timestamp NULL DEFAULT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `mensagem_erro` text DEFAULT NULL,
  `parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametros`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `transacoes_jogadores`
--

CREATE TABLE `transacoes_jogadores` (
  `id` int(11) NOT NULL,
  `jogador_id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `tipo` enum('CREDITO','DEBITO','ACERTO_POSITIVO','ACERTO_NEGATIVO','QUITACAO') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `data_transacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `quitado` tinyint(1) DEFAULT 0,
  `data_quitacao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `unidades`
--

CREATE TABLE `unidades` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `codigo_acesso` varchar(8) NOT NULL,
  `status` enum('Ativa','Inativa') DEFAULT 'Ativa',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `unidades`
--

INSERT INTO `unidades` (`id`, `nome`, `telefone`, `codigo_acesso`, `status`, `data_criacao`) VALUES
(6, 'Poker Base TESTE', '84994187843', 'ZU35QSTI', 'Ativa', '2025-07-14 00:21:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_ultimo_acesso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `cpf`, `email`, `senha`, `status`, `data_criacao`, `data_ultimo_acesso`) VALUES
(3, 'João Silva TESTE', '11122233344', '11122233344@temp.com', '$2y$10$4.QVFW8fVsVb6FSSp2uChu6boiD5zd1g66PcV6LixmnZqCPVUBDTW', 'Ativo', '2025-07-13 23:28:58', NULL),
(12, 'Lucas Andrade TESTE', '12924554466', 'teste@teste.com', '$2y$10$U.EMueboZN4Gwp1zepgVn.bWLCkt7ghDkbraIUNznCLulAg3n0K02', 'Ativo', '2025-07-14 00:21:43', NULL),
(13, 'Caixa Teste 1', '90000000001', 'caixa1@teste.com', '$2y$10$NK/MzBhWYuWTIBw.A46rE.JKhS8T2Zz3JhACc4.5QHywk7tUIDTca', 'Ativo', '2025-07-14 00:23:19', NULL),
(14, 'Caixa Teste 2', '90000000002', 'caixa2@teste.com', '$2y$10$mT6wP5Txcn/fuQnDnRST6.B1fbIgqpH8wsF4X7b45tai41/fZaFa.', 'Ativo', '2025-07-14 00:23:19', NULL),
(15, 'Caixa Teste 3', '90000000003', 'caixa3@teste.com', '$2y$10$MgeFyCyy.MEZPwJKEaa6Ou9llwE8QAJqL7n1fahDVnBiWpY86aRzK', 'Ativo', '2025-07-14 00:23:19', NULL),
(16, 'Caixa Teste 4', '90000000004', 'caixa4@teste.com', '$2y$10$m2szOZKfGMqkTefwnmUQvOzdmge7yPpt2c04iNDV2mcNCOUeITnve', 'Ativo', '2025-07-14 00:23:20', NULL),
(17, 'Caixa Teste 5', '90000000005', 'caixa5@teste.com', '$2y$10$2N/WxOBRhY0jRsyaB3sPyenRibKoaUY7SVpkc7vritL6rlGO/C9iO', 'Ativo', '2025-07-14 00:23:20', NULL),
(18, 'Caixa Teste 6', '90000000006', 'caixa6@teste.com', '$2y$10$/SiPF/YhoEK5XOqhlQdPsONCq2AzWxNTIiM009KZIDyUt06toQCSS', 'Ativo', '2025-07-14 00:23:20', NULL),
(19, 'Caixa Teste 7', '90000000007', 'caixa7@teste.com', '$2y$10$TfS/50ZYy0HLRcgAyk2bx.CdgiR0F3/.1XoFndI..NPAT3wtCEERu', 'Ativo', '2025-07-14 00:23:20', NULL),
(20, 'Caixa Teste 8', '90000000008', 'caixa8@teste.com', '$2y$10$T4qJ/z8vdazgjVsjWkTrAOQ1chfzWCMkQRBkkyC0e3tpfPwdELPBi', 'Ativo', '2025-07-14 00:23:20', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aprovacoes`
--
ALTER TABLE `aprovacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `gestor_id` (`gestor_id`);

--
-- Índices de tabela `aprovacoes_acesso`
--
ALTER TABLE `aprovacoes_acesso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `gestor_id` (`gestor_id`);

--
-- Índices de tabela `aprovacoes_acesso_historico`
--
ALTER TABLE `aprovacoes_acesso_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aprovacao_id` (`aprovacao_id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `funcionario_aprovador_id` (`funcionario_aprovador_id`);

--
-- Índices de tabela `caixas`
--
ALTER TABLE `caixas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unidade_id` (`unidade_id`),
  ADD KEY `funcionario_abertura_id` (`funcionario_abertura_id`),
  ADD KEY `funcionario_fechamento_id` (`funcionario_fechamento_id`);

--
-- Índices de tabela `caixinhas`
--
ALTER TABLE `caixinhas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `funcionario_criacao_id` (`funcionario_criacao_id`);

--
-- Índices de tabela `caixinhas_inclusoes`
--
ALTER TABLE `caixinhas_inclusoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixinha_id` (`caixinha_id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `fichas_denom`
--
ALTER TABLE `fichas_denom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unidade_id` (`unidade_id`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_unidade_cargo` (`usuario_id`,`unidade_id`,`cargo`),
  ADD KEY `unidade_id` (`unidade_id`);

--
-- Índices de tabela `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `inventario_fichas`
--
ALTER TABLE `inventario_fichas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `ficha_denom_id` (`ficha_denom_id`);

--
-- Índices de tabela `jogadores`
--
ALTER TABLE `jogadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unidade_id` (`unidade_id`),
  ADD KEY `funcionario_cadastro_id` (`funcionario_cadastro_id`);

--
-- Índices de tabela `movimentacoes_fichas`
--
ALTER TABLE `movimentacoes_fichas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `jogador_id` (`jogador_id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `ficha_denom_id` (`ficha_denom_id`);

--
-- Índices de tabela `rake`
--
ALTER TABLE `rake`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `relatorios_historico`
--
ALTER TABLE `relatorios_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`),
  ADD KEY `unidade_id` (`unidade_id`);

--
-- Índices de tabela `transacoes_jogadores`
--
ALTER TABLE `transacoes_jogadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jogador_id` (`jogador_id`),
  ADD KEY `caixa_id` (`caixa_id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_acesso` (`codigo_acesso`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aprovacoes`
--
ALTER TABLE `aprovacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aprovacoes_acesso`
--
ALTER TABLE `aprovacoes_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `aprovacoes_acesso_historico`
--
ALTER TABLE `aprovacoes_acesso_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caixas`
--
ALTER TABLE `caixas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caixinhas`
--
ALTER TABLE `caixinhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caixinhas_inclusoes`
--
ALTER TABLE `caixinhas_inclusoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fichas_denom`
--
ALTER TABLE `fichas_denom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inventario_fichas`
--
ALTER TABLE `inventario_fichas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jogadores`
--
ALTER TABLE `jogadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movimentacoes_fichas`
--
ALTER TABLE `movimentacoes_fichas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rake`
--
ALTER TABLE `rake`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_historico`
--
ALTER TABLE `relatorios_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `transacoes_jogadores`
--
ALTER TABLE `transacoes_jogadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aprovacoes`
--
ALTER TABLE `aprovacoes`
  ADD CONSTRAINT `aprovacoes_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aprovacoes_ibfk_2` FOREIGN KEY (`gestor_id`) REFERENCES `funcionarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `aprovacoes_acesso`
--
ALTER TABLE `aprovacoes_acesso`
  ADD CONSTRAINT `aprovacoes_acesso_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aprovacoes_acesso_ibfk_2` FOREIGN KEY (`gestor_id`) REFERENCES `funcionarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `aprovacoes_acesso_historico`
--
ALTER TABLE `aprovacoes_acesso_historico`
  ADD CONSTRAINT `aprovacoes_acesso_historico_ibfk_1` FOREIGN KEY (`aprovacao_id`) REFERENCES `aprovacoes_acesso` (`id`),
  ADD CONSTRAINT `aprovacoes_acesso_historico_ibfk_2` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  ADD CONSTRAINT `aprovacoes_acesso_historico_ibfk_3` FOREIGN KEY (`funcionario_aprovador_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `caixas`
--
ALTER TABLE `caixas`
  ADD CONSTRAINT `caixas_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `caixas_ibfk_2` FOREIGN KEY (`funcionario_abertura_id`) REFERENCES `funcionarios` (`id`),
  ADD CONSTRAINT `caixas_ibfk_3` FOREIGN KEY (`funcionario_fechamento_id`) REFERENCES `funcionarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `caixinhas`
--
ALTER TABLE `caixinhas`
  ADD CONSTRAINT `caixinhas_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `caixinhas_ibfk_2` FOREIGN KEY (`funcionario_criacao_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `caixinhas_inclusoes`
--
ALTER TABLE `caixinhas_inclusoes`
  ADD CONSTRAINT `caixinhas_inclusoes_ibfk_1` FOREIGN KEY (`caixinha_id`) REFERENCES `caixinhas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `caixinhas_inclusoes_ibfk_2` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `fichas_denom`
--
ALTER TABLE `fichas_denom`
  ADD CONSTRAINT `fichas_denom_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD CONSTRAINT `funcionarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `funcionarios_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `inventario_fichas`
--
ALTER TABLE `inventario_fichas`
  ADD CONSTRAINT `inventario_fichas_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_fichas_ibfk_2` FOREIGN KEY (`ficha_denom_id`) REFERENCES `fichas_denom` (`id`);

--
-- Restrições para tabelas `jogadores`
--
ALTER TABLE `jogadores`
  ADD CONSTRAINT `jogadores_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jogadores_ibfk_2` FOREIGN KEY (`funcionario_cadastro_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `movimentacoes_fichas`
--
ALTER TABLE `movimentacoes_fichas`
  ADD CONSTRAINT `movimentacoes_fichas_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_fichas_ibfk_2` FOREIGN KEY (`jogador_id`) REFERENCES `jogadores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_fichas_ibfk_3` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  ADD CONSTRAINT `movimentacoes_fichas_ibfk_4` FOREIGN KEY (`ficha_denom_id`) REFERENCES `fichas_denom` (`id`);

--
-- Restrições para tabelas `rake`
--
ALTER TABLE `rake`
  ADD CONSTRAINT `rake_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rake_ibfk_2` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);

--
-- Restrições para tabelas `relatorios_historico`
--
ALTER TABLE `relatorios_historico`
  ADD CONSTRAINT `relatorios_historico_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  ADD CONSTRAINT `relatorios_historico_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `transacoes_jogadores`
--
ALTER TABLE `transacoes_jogadores`
  ADD CONSTRAINT `transacoes_jogadores_ibfk_1` FOREIGN KEY (`jogador_id`) REFERENCES `jogadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transacoes_jogadores_ibfk_2` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transacoes_jogadores_ibfk_3` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
