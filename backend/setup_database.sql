-- Script para configurar o banco de dados ERP CISLAMO
-- Execute no phpMyAdmin (SQL tab) ou via linha de comando: mysql -u root -p < setup_database.sql

-- Criar o banco de dados (se ainda não existir)
CREATE DATABASE IF NOT EXISTS erp_cislamo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Remover o usuário se existir (para recriar)
DROP USER IF EXISTS 'erp_user'@'localhost';

-- Criar o usuário com a senha correta
CREATE USER 'erp_user'@'localhost' IDENTIFIED BY 'erp_password';

-- Dar todas as permissões ao usuário no banco de dados
GRANT ALL PRIVILEGES ON erp_cislamo.* TO 'erp_user'@'localhost';

-- Aplicar as mudanças
FLUSH PRIVILEGES;

-- Mostrar confirmação
SELECT 'Database and user created successfully!' AS Status;

