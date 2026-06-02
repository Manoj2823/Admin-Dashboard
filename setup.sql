-- Run this in phpMyAdmin > SQL tab > click Go

CREATE DATABASE IF NOT EXISTS login_db;

USE login_db;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Test admin account  →  email: admin@example.com  |  password: admin123
-- MD5 hash kept here; login.php now supports both MD5 (legacy) and bcrypt automatically
INSERT INTO users (username, email, password, role)
VALUES ('Admin', 'admin@example.com', MD5('admin123'), 'admin');
