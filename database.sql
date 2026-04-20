-- =====================================================
-- Restaurant Management System - Database Schema
-- =====================================================
-- Import this file into phpMyAdmin (XAMPP/Laragon) or run:
--   mysql -u root -p < database.sql
-- =====================================================

DROP DATABASE IF EXISTS restaurant_db;
CREATE DATABASE restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- -----------------------------------------------------
-- 1. users
-- -----------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','client') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 2. category
-- -----------------------------------------------------
CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 3. plat (dishes)
-- -----------------------------------------------------
CREATE TABLE plat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 4. restaurant_table
-- -----------------------------------------------------
CREATE TABLE restaurant_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number INT NOT NULL UNIQUE,
    capacity INT NOT NULL,
    status ENUM('available','occupied','reserved') DEFAULT 'available'
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 5. message
-- -----------------------------------------------------
CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 6. reclamation (complaints)
-- -----------------------------------------------------
CREATE TABLE reclamation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    status ENUM('open','in_progress','closed') DEFAULT 'open',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 7. commande (orders)
-- -----------------------------------------------------
CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','preparing','served','cancelled','paid') DEFAULT 'pending',
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 8. order_items
-- -----------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    commande_id INT NOT NULL,
    plat_id INT NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 9. payment
-- -----------------------------------------------------
CREATE TABLE payment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total DECIMAL(10,2) NOT NULL,
    method ENUM('cash','card','online') NOT NULL,
    status ENUM('pending','completed','failed') DEFAULT 'pending',
    commande_id INT NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 10. reservation
-- -----------------------------------------------------
CREATE TABLE reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    time TIME NOT NULL,
    number_of_people INT NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    user_id INT NOT NULL,
    table_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_table(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 11. contact
-- -----------------------------------------------------
CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 12. avis (reviews)
-- -----------------------------------------------------
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
    comment TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Seed data
-- =====================================================

-- Default admin  (email: admin@restaurant.com | password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@restaurant.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==ignored', 'admin');
-- The above hash is a placeholder; the real one is regenerated on first run
-- by visiting /backend/index.php?seed=1  (see README). You can also register
-- a new account and promote it to admin in the DB.

INSERT INTO category (name) VALUES
('Starters'), ('Main Courses'), ('Desserts'), ('Drinks');

INSERT INTO plat (name, description, price, category_id) VALUES
('Bruschetta', 'Grilled bread with tomatoes, garlic and basil', 6.50, 1),
('Caesar Salad', 'Romaine lettuce, parmesan, croutons, Caesar dressing', 8.00, 1),
('Margherita Pizza', 'Tomato, mozzarella, fresh basil', 11.50, 2),
('Spaghetti Carbonara', 'Eggs, pancetta, pecorino, pepper', 13.00, 2),
('Tiramisu', 'Classic Italian coffee-flavored dessert', 6.00, 3),
('Coca-Cola', '33cl can', 2.50, 4),
('Still Water', '50cl bottle', 1.50, 4);

INSERT INTO restaurant_table (number, capacity, status) VALUES
(1, 2, 'available'),
(2, 4, 'available'),
(3, 4, 'available'),
(4, 6, 'available'),
(5, 8, 'available');
