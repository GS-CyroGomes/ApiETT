DROP DATABASE IF EXISTS temora_coleta;
CREATE DATABASE temora_coleta;
USE temora_coleta;

-- Verifica e cria usuário se necessário
SET @user_count := (SELECT COUNT(*) FROM mysql.user WHERE user = 'web' AND host = 'localhost');
SET @create_user := IF(@user_count = 0, 'CREATE USER ''web''@''localhost'' IDENTIFIED BY ''web'';', 'SELECT 1;');
PREPARE stmt FROM @create_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Concede permissões ao usuário
SET @grant_sql := IF(@user_count > 0 OR EXISTS(SELECT 1 FROM mysql.user WHERE user = 'web' AND host = 'localhost'),
    'GRANT ALL PRIVILEGES ON \`$banco\`.* TO ''web''@''localhost'';','SELECT 1;');
PREPARE stmt FROM @grant_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @user_count := (SELECT COUNT(*) FROM mysql.user WHERE user = 'web' AND host = '%');
SET @create_user := IF(@user_count = 0, 'CREATE USER ''web''@''%'' IDENTIFIED BY ''web'';', 'SELECT 1;');
PREPARE stmt FROM @create_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Concede permissões ao usuário
SET @grant_sql := IF(@user_count > 0 OR EXISTS(SELECT 1 FROM mysql.user WHERE user = 'web' AND host = '%'),
    'GRANT ALL PRIVILEGES ON \`$banco\`.* TO ''web''@''%'';','SELECT 1;');
PREPARE stmt FROM @grant_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Cria usuário , verifica, concede e permissões se necessário
CREATE USER IF NOT EXISTS 'web'@'%' IDENTIFIED BY 'web';
GRANT ALL PRIVILEGES ON *.* TO 'web'@'%';
FLUSH PRIVILEGES;


CREATE TABLE `pessoa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `data_nascimento` date NOT NULL,
  `rg` varchar(9) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `orgao_emissor` varchar(100) NOT NULL,
  `uf_emissor` varchar(2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  UNIQUE KEY `unique_cpf` (`cpf`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `usuario` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_pessoa` INT NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_id_pessoa` (`id_pessoa`),
    CONSTRAINT `fk_usuario_pessoa`
        FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa`(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
