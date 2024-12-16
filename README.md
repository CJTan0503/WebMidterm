# WebMidterm
CREATE DATABASE bmi_system;

USE bmi_system;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- BMI Results Table
CREATE TABLE bmi_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    height FLOAT NOT NULL,
    weight FLOAT NOT NULL,
    bmi FLOAT NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
