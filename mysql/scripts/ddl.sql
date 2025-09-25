-- Drop e cria banco
DROP DATABASE IF EXISTS temora_coleta;
CREATE DATABASE temora_coleta CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE temora_coleta;

-- Cria usuário se não existir e concede permissões
CREATE USER IF NOT EXISTS 'web'@'%' IDENTIFIED BY 'web';
GRANT ALL PRIVILEGES ON temora_coleta.* TO 'web'@'%';
FLUSH PRIVILEGES;

-- Tabela pessoa
CREATE TABLE IF NOT EXISTS `pessoa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `data_nascimento` date NOT NULL,
  `rg` varchar(9) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `orgao_emissor` varchar(100) NOT NULL,
  `uf_emissor` varchar(2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabela usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pessoa` INT NOT NULL,
  `password` VARBINARY(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id_pessoa` (`id_pessoa`),
  CONSTRAINT `fk_usuario_pessoa`
    FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
