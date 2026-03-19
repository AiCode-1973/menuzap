-- Banco de Dados: dema5738_menuzap

CREATE TABLE IF NOT EXISTS `restaurante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `cor_primaria` varchar(20) DEFAULT '#EF4444',
  `cor_secundaria` varchar(20) DEFAULT '#DC2626',
  `horario_funcionamento` varchar(255) DEFAULT 'Aberto de Terça a Domingo',
  `mensagem_boasvindas` text DEFAULT NULL,
  `ocultar_nome` tinyint(1) DEFAULT 0,
  `ocultar_mensagem` tinyint(1) DEFAULT 0,
  `ocultar_logo` tinyint(1) DEFAULT 0,
  `ocultar_banner` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurante` (`id`, `nome`, `logo`, `banner`, `cor_primaria`, `cor_secundaria`, `horario_funcionamento`, `mensagem_boasvindas`, `ocultar_nome`, `ocultar_mensagem`, `ocultar_logo`, `ocultar_banner`, `ativo`) 
VALUES (1, 'Meu Restaurante', NULL, NULL, '#EF4444', '#DC2626', 'Aberto de Terça a Domingo', 'Bem-vindo ao nosso cardápio digital!', 0, 0, 0, 0, 1) 
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(255) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `destaque` tinyint(1) DEFAULT 0,
  `esgotado` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mesas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `qrcode_url` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesa_id` int(11) DEFAULT NULL,
  `cliente_nome` varchar(100) DEFAULT NULL,
  `status` enum('recebido','em_preparo','pronto','entregue','cancelado') DEFAULT 'recebido',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`mesa_id`) REFERENCES `mesas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `observacao` varchar(255) DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produto_id`) REFERENCES `produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('admin','garcom') DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir usuário Admin padrão na primeira execução: (Senha: admin123)
INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `perfil`) 
SELECT 'Administrador', 'admin@admin.com', '$2y$10$QDbfL3GyMO5NGVGglJsyl.3Ku3eKItQkRqtdV.gCRJaCs3ba9uGUoK', 'admin' 
WHERE NOT EXISTS (SELECT id FROM `usuarios` WHERE email = 'admin@admin.com');
