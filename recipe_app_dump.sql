-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25-Jul-2025 às 16:47
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `app_receitas`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Sobremesa'),
(2, 'Doce Caseiro');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ingredientes`
--

CREATE TABLE `ingredientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `ingredientes`
--

INSERT INTO `ingredientes` (`id`, `nome`) VALUES
(1, 'Açúcar'),
(2, 'Leite'),
(3, 'Leite Condensado'),
(4, 'Leite em Pó'),
(5, 'Chocolate em Pó');

-- --------------------------------------------------------

--
-- Estrutura da tabela `receitas`
--

CREATE TABLE `receitas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tempo_preparacao` int(11) DEFAULT NULL,
  `doses` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `receitas`
--

INSERT INTO `receitas` (`id`, `nome`, `descricao`, `tempo_preparacao`, `doses`) VALUES
(1, 'Doce de leite cremoso', 'Fácil e rápido', 15, 3),
(2, 'Leite Ninho', NULL, NULL, NULL),
(7, 'Pão de queijo', 'O gostinho de mineiro', 20, 6);

-- --------------------------------------------------------

--
-- Estrutura da tabela `receita_categoria`
--

CREATE TABLE `receita_categoria` (
  `receita_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `receita_categoria`
--

INSERT INTO `receita_categoria` (`receita_id`, `categoria_id`) VALUES
(1, 1),
(2, 2),
(7, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `receita_ingredientes`
--

CREATE TABLE `receita_ingredientes` (
  `receita_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `quantidade` varchar(50) DEFAULT NULL,
  `unidade_medida` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `receita_ingredientes`
--

INSERT INTO `receita_ingredientes` (`receita_id`, `ingrediente_id`, `quantidade`, `unidade_medida`) VALUES
(1, 1, '200', 'g'),
(1, 2, '500', 'ml'),
(1, 3, '300', 'g'),
(2, 4, '300', 'g'),
(2, 5, '100', 'g'),
(7, 1, '2', '2'),
(7, 2, '150', 'ml');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `receitas`
--
ALTER TABLE `receitas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `receita_categoria`
--
ALTER TABLE `receita_categoria`
  ADD PRIMARY KEY (`receita_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `receita_ingredientes`
--
ALTER TABLE `receita_ingredientes`
  ADD PRIMARY KEY (`receita_id`,`ingrediente_id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `receitas`
--
ALTER TABLE `receitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `receita_categoria`
--
ALTER TABLE `receita_categoria`
  ADD CONSTRAINT `receita_categoria_ibfk_1` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`),
  ADD CONSTRAINT `receita_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Limitadores para a tabela `receita_ingredientes`
--
ALTER TABLE `receita_ingredientes`
  ADD CONSTRAINT `receita_ingredientes_ibfk_1` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`),
  ADD CONSTRAINT `receita_ingredientes_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;