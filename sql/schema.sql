CREATE DATABASE IF NOT EXISTS sector_management_tool;

USE sector_management_tool;

CREATE TABLE IF NOT EXISTS sectors (
    sector_id INT PRIMARY KEY,
    sector_name VARCHAR(255) NOT NULL,
    sector_parent_id INT DEFAULT NULL,
    FOREIGN KEY (sector_parent_id) REFERENCES sectors(sector_id) ON DELETE SET NULL
);


CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    user_agree_to_terms BOOLEAN NOT NULL DEFAULT 0,
    user_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS user_sectors (
    user_sectors_id INT AUTO_INCREMENT PRIMARY KEY,
    user_sectors_user_id INT NOT NULL,
    user_sectors_sector_id INT NOT NULL,
    FOREIGN KEY (user_sectors_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (user_sectors_sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
);