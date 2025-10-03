CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255),
    tipo_permissao ENUM('boletim', 'hidrologico') NOT NULL
);

INSERT INTO usuarios(id, nome, email, senha, tipo_permissao) VALUES('','Corina','corina@hotmail.com',sha1('1234'),'boletim');

Inserir usuário

INSERT INTO admin (id, usuario, senha, tipo_permissao) 
VALUES (NULL, 'Corina', SHA1('1234'), 'hidrologico');


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/06/2025 às 21:53
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sssp2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `admin`
--

INSERT INTO `admin` (`id`, `usuario`, `senha`) VALUES
(1, 'admin', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220');

-- --------------------------------------------------------

--
-- Estrutura para tabela `boletins`
--

CREATE TABLE `boletins` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `boletins`
--

INSERT INTO `boletins` (`id`, `nome`, `arquivo`, `data_upload`) VALUES
(1, 'boletim', '6846db3494f68.pdf', '2025-06-09 13:01:40'),
(2, 'boletim', '6846ed0f75fa2.pdf', '2025-06-09 14:17:51'),
(3, 'teste', '6846ee4e8b685.pdf', '2025-06-09 14:23:10'),
(4, 'teste', '6846ef0857aeb.pdf', '2025-06-09 14:26:16'),
(5, 'boletim', '684711e93b365.pdf', '2025-06-09 16:55:05'),
(6, 'boletim', '68472b61792ae.pdf', '2025-06-09 18:43:45'),
(7, 'Boletim ', '6849b5181f284.pdf', '2025-06-11 16:55:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `boletinsmensais`
--

CREATE TABLE `boletinsmensais` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `boletinsmensais`
--

INSERT INTO `boletinsmensais` (`id`, `nome`, `arquivo`, `data_upload`) VALUES
(0, 'Boletim Mensal', '6849c19fcfeb7.pdf', '2025-06-11 17:49:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `up_boletim_hidro`
--

CREATE TABLE `up_boletim_hidro` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `up_boletim_hidro`
--

INSERT INTO `up_boletim_hidro` (`id`, `nome`, `arquivo`, `data_upload`) VALUES
(0, 'boletim', '684af7c228b8a.pdf', '2025-06-12 15:52:34');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `boletins`
--
ALTER TABLE `boletins`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `boletins`
--
ALTER TABLE `boletins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
