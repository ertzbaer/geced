-- Beispiel-Datenbank-Schema für Geex Dashboard
-- Führen Sie dieses SQL-Skript in Ihrer MySQL-Datenbank aus

CREATE DATABASE IF NOT EXISTS geex_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE geex_dashboard;

-- Benutzer-Tabelle
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog-Posts Tabelle
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nachrichten-Tabelle
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ToDo-Tabelle
CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Beispiel-Daten einfügen
INSERT INTO users (username, email, password) VALUES 
('admin', 'admin@geex.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- Password: password
('demo_user', 'demo@geex.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO blog_posts (title, content, author_id, status) VALUES
('Willkommen zum Geex Dashboard', 'Dies ist ein Beispiel-Blogpost mit dynamischem Inhalt aus der Datenbank.', 1, 'published'),
('PHP Template System', 'Dieses Dashboard verwendet ein dynamisches PHP-Template-System mit AJAX-Loading.', 1, 'published');

INSERT INTO todos (user_id, title, description, status, priority) VALUES
(1, 'Dashboard testen', 'Alle Funktionen des neuen PHP-Templates überprüfen', 'pending', 'high'),
(1, 'Datenbank konfigurieren', 'MySQL-Verbindung einrichten', 'completed', 'high');