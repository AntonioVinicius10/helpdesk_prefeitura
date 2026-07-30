-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30-Jul-2026 às 17:09
-- Versão do servidor: 10.4.11-MariaDB
-- versão do PHP: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `helpdesk_prefeitura`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`, `ativo`) VALUES
(1, 'Hardware / Computador', 'Problemas com gabinete, monitor, mouse, teclado, etc.', 1),
(2, 'Sistemas / Software', 'Erros em sistemas internos, navegadores ou programas', 1),
(3, 'Impressora / Escâner', 'Impressora sem papel, atolamento ou sem comunicação', 1),
(4, 'Rede / Internet', 'Sem acesso à rede local ou internet', 1),
(5, 'Hardware / Equipamento', NULL, 1),
(6, 'Sistemas / Software', NULL, 1),
(7, 'Rede / Internet', NULL, 1),
(8, 'Impressora', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `chamados`
--

CREATE TABLE `chamados` (
  `id` int(11) NOT NULL,
  `protocolo` varchar(20) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `status` enum('novo','aberto','em_andamento','resolvido') NOT NULL DEFAULT 'novo',
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `usuario_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fechado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `chamados_respostas`
--

CREATE TABLE `chamados_respostas` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `criado_em` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `hostname` varchar(100) NOT NULL,
  `cpu_modelo` varchar(150) DEFAULT NULL,
  `gpu_modelo` varchar(150) DEFAULT NULL,
  `ram_total_mb` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuário principal ou dono do dispositivo',
  `setor_id` int(11) DEFAULT NULL COMMENT 'Setor onde o PC está alocado',
  `setor_nome` varchar(100) DEFAULT NULL,
  `primeiro_acesso` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acesso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `dispositivos_telemetria`
--

CREATE TABLE `dispositivos_telemetria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `ram_livre_mb` int(11) NOT NULL,
  `disco_livre_gb` int(11) DEFAULT NULL,
  `alertas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ex: ["CRITICAL_LOW_RAM", "CRITICAL_LOW_DISK"]' CHECK (json_valid(`alertas`)),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `interacoes_chamado`
--

CREATE TABLE `interacoes_chamado` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `secretarias_setores`
--

CREATE TABLE `secretarias_setores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sigla` varchar(20) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `secretarias_setores`
--

INSERT INTO `secretarias_setores` (`id`, `nome`, `sigla`, `ativo`, `criado_em`) VALUES
(1, 'Secretaria de Administração e TI', 'TI', 1, '2026-07-23 02:36:02'),
(2, 'Secretaria de Educação', 'SEDUC', 1, '2026-07-23 02:36:02'),
(5, 'TI prefeitura de Borborema', 'TEC TI', 1, '2026-07-24 23:08:40'),
(6, 'Almoxarifado borbo', 'AM', 1, '2026-07-27 16:30:20'),
(7, 'obras e postura', 'OP', 1, '2026-07-27 17:42:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `perfil` enum('admin','tecnico','usuario') NOT NULL DEFAULT 'usuario',
  `setor_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `cpf`, `telefone`, `perfil`, `setor_id`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(1, 'testadmin', 'testadmin@borborema.sp.gov.br', '$2y$10$FFnSpFO6YqO6RSNFFeQzmOoXRq.FV6qvAOy1coGhYhkF1NgJfgXPS', NULL, '(31) 99999-9999', 'admin', 1, 1, '2026-07-23 02:36:02', '2026-07-25 01:05:19');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `chamados`
--
ALTER TABLE `chamados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `protocolo` (`protocolo`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `tecnico_id` (`tecnico_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hostname` (`hostname`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_setor_id` (`setor_id`);

--
-- Índices para tabela `dispositivos_telemetria`
--
ALTER TABLE `dispositivos_telemetria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispositivo_id` (`dispositivo_id`),
  ADD KEY `idx_dispositivo_data` (`dispositivo_id`,`criado_em`),
  ADD KEY `idx_criado_em` (`criado_em`);

--
-- Índices para tabela `interacoes_chamado`
--
ALTER TABLE `interacoes_chamado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `secretarias_setores`
--
ALTER TABLE `secretarias_setores`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `setor_id` (`setor_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de tabela `dispositivos_telemetria`
--
ALTER TABLE `dispositivos_telemetria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de tabela `interacoes_chamado`
--
ALTER TABLE `interacoes_chamado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `secretarias_setores`
--
ALTER TABLE `secretarias_setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `chamados`
--
ALTER TABLE `chamados`
  ADD CONSTRAINT `chamados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chamados_ibfk_2` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chamados_ibfk_3` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Limitadores para a tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  ADD CONSTRAINT `chamados_respostas_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chamados_respostas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `fk_dispositivos_setores` FOREIGN KEY (`setor_id`) REFERENCES `secretarias_setores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dispositivos_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `dispositivos_telemetria`
--
ALTER TABLE `dispositivos_telemetria`
  ADD CONSTRAINT `fk_telemetria_dispositivos` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `interacoes_chamado`
--
ALTER TABLE `interacoes_chamado`
  ADD CONSTRAINT `interacoes_chamado_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interacoes_chamado_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `secretarias_setores` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
