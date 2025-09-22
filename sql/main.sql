--
-- Create database
--
DROP DATABASE IF EXISTS datebase_name;
CREATE DATABASE IF NOT EXISTS datebase_name
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE datebase_name;

--
-- Create table for users
--
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  user_id CHAR(36) PRIMARY KEY,
  name VARCHAR(100),
  username VARCHAR(32) NOT NULL UNIQUE,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  bio TEXT,
  location VARCHAR(150),
  is_verified BOOLEAN DEFAULT 0,
  token VARCHAR(64),
  token_expires_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

--
-- Create table for login attempts
--
DROP TABLE IF EXISTS login_attempts;
CREATE TABLE login_attempts (
  attempt_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  ip_address VARCHAR(45),
  success BOOLEAN DEFAULT 0,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_user_id ON login_attempts (user_id);
CREATE INDEX idx_ip_address ON login_attempts (ip_address);

--
-- Create table for temporary login bans
--
DROP TABLE IF EXISTS temp_bans;
CREATE TABLE temp_bans (
  ban_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  ip_address VARCHAR(45),
  ban_until TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 MINUTE),
  banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_id_ip ON temp_bans (user_id, ip_address);

--
-- Create table for permanent login bans
--
DROP TABLE IF EXISTS perma_bans;
CREATE TABLE perma_bans (
  ban_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  ip_address VARCHAR(45),
  banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_id_ip ON perma_bans (user_id, ip_address);

--
-- Create table for successful logins
--
DROP TABLE IF EXISTS successful_logins;
CREATE TABLE successful_logins (
  login_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  ip_address VARCHAR(45),
  token VARCHAR(64),
  first_success TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE unique_user_ip (user_id, ip_address)
);

--
-- Create table for updating email
--
DROP TABLE IF EXISTS new_email;
CREATE TABLE new_email (
  update_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  new_email VARCHAR(254) NOT NULL UNIQUE,
  old_email VARCHAR(254) NOT NULL,
  revert_token VARCHAR(64),
  reverted BOOLEAN DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

--
-- Create table for updating password
--
DROP TABLE IF EXISTS new_password;
CREATE TABLE new_password (
  update_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  token VARCHAR(64),
  token_expires_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

--
-- Create table for old passwords
--
DROP TABLE IF EXISTS old_passwords;
CREATE TABLE old_passwords (
  password_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

--
-- Create table for images
--
DROP TABLE IF EXISTS images;
CREATE TABLE images (
  image_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  path VARCHAR(250) NOT NULL,
  mime_type VARCHAR(13) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_id_path ON perma_bans (user_id, path);
