-- CrypyedManga MySQL Schema
-- Create database (optional)
-- CREATE DATABASE IF NOT EXISTS crypyedmanga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE crypyedmanga;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    bio TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mangas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mangadex_id CHAR(36) NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    author_id INT NULL,
    cover_image VARCHAR(255) NULL,
    release_date DATE NULL,
    language VARCHAR(10) NOT NULL DEFAULT 'en',
    views INT NOT NULL DEFAULT 0,
    popularity_score INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mangas_author FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manga_genres (
    manga_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (manga_id, genre_id),
    CONSTRAINT fk_manga_genres_manga FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
    CONSTRAINT fk_manga_genres_genre FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manga_id INT NOT NULL,
    mangadex_chapter_id CHAR(36) NULL UNIQUE,
    chapter_number VARCHAR(20) NOT NULL,
    title VARCHAR(255) NULL,
    volume VARCHAR(10) NULL,
    pages_count INT NOT NULL DEFAULT 0,
    upload_source ENUM('mangadex','local') NOT NULL DEFAULT 'local',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_chapters_manga FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapter_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT NOT NULL,
    page_number INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    width INT NULL,
    height INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chapter_images_chapter FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
    INDEX idx_chapter_images_chapter_page (chapter_id, page_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new','replied') NOT NULL DEFAULT 'new',
    reply_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    manga_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bookmark (user_id, manga_id),
    CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_manga FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reading_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    manga_id INT NOT NULL,
    chapter_id INT NOT NULL,
    last_page INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_reading_history (user_id, chapter_id),
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_manga FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_chapter FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed data
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9p3ii0.2U7lrQKZ8o6hZGa', 1), -- password: password
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9p3ii0.2U7lrQKZ8o6hZGa', 0);

INSERT INTO authors (name) VALUES
('K. Nakamura'), ('A. Sato'), ('M. Tanaka'), ('R. Suzuki'), ('Y. Takahashi');

INSERT INTO genres (name) VALUES
('Action'),('Adventure'),('Comedy'),('Drama'),('Fantasy'),('Sci-Fi'),('Romance'),('Slice of Life'),('Mystery');

-- Sample mangas (covers and images can be imported via retriever)
INSERT INTO mangas (mangadex_id, slug, title, description, author_id, cover_image, release_date, language, views, popularity_score) VALUES
(NULL, 'neon-samurai', 'Neon Samurai', 'A lone cyber-samurai roams the neon-lit megacity.', 1, 'uploads/mangas/placeholder.svg', '2023-01-01', 'en', 0, 0),
(NULL, 'quantum-hearts', 'Quantum Hearts', 'Love and physics collide in a parallel universe.', 2, 'uploads/mangas/placeholder.svg', '2022-11-10', 'en', 0, 0),
(NULL, 'mecha-dreamers', 'Mecha Dreamers', 'Teens pilot sentient mecha to defend Earth.', 3, 'uploads/mangas/placeholder.svg', '2021-06-15', 'en', 0, 0),
(NULL, 'arcane-bytes', 'Arcane Bytes', 'Magic meets code in a world of arcane hackers.', 4, 'uploads/mangas/placeholder.svg', '2020-09-30', 'en', 0, 0),
(NULL, 'ghosts-in-the-wifi', 'Ghosts in the WiFi', 'Spirits haunt the network and a hacker must appease them.', 5, 'uploads/mangas/placeholder.svg', '2019-12-05', 'en', 0, 0);

-- Map genres to sample mangas
INSERT INTO manga_genres (manga_id, genre_id) VALUES
(1, 1),(1, 6), -- Neon Samurai: Action, Sci-Fi
(2, 6),(2, 7), -- Quantum Hearts: Sci-Fi, Romance
(3, 1),(3, 5), -- Mecha Dreamers: Action, Fantasy
(4, 5),(4, 6), -- Arcane Bytes: Fantasy, Sci-Fi
(5, 4),(5, 9); -- Ghosts in the WiFi: Drama, Mystery

-- Sample chapters for each manga (3 per manga)
INSERT INTO chapters (manga_id, chapter_number, title, pages_count, upload_source) VALUES
(1, '1', 'Awakening', 3, 'local'),
(1, '2', 'City of Neon', 3, 'local'),
(1, '3', 'Cyber Duel', 3, 'local'),
(2, '1', 'Entanglement', 3, 'local'),
(2, '2', 'Superposition', 3, 'local'),
(2, '3', 'Collapse', 3, 'local'),
(3, '1', 'Pilot', 3, 'local'),
(3, '2', 'Sync', 3, 'local'),
(3, '3', 'Overdrive', 3, 'local'),
(4, '1', 'Compile', 3, 'local'),
(4, '2', 'Execute', 3, 'local'),
(4, '3', 'Refactor', 3, 'local'),
(5, '1', 'Ping', 3, 'local'),
(5, '2', 'Handshake', 3, 'local'),
(5, '3', 'Packet Loss', 3, 'local');

-- Chapter images placeholders (every page points to placeholder in uploads)
INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES
(1,1,'uploads/mangas/placeholder.svg'),(1,2,'uploads/mangas/placeholder.svg'),(1,3,'uploads/mangas/placeholder.svg'),
(2,1,'uploads/mangas/placeholder.svg'),(2,2,'uploads/mangas/placeholder.svg'),(2,3,'uploads/mangas/placeholder.svg'),
(3,1,'uploads/mangas/placeholder.svg'),(3,2,'uploads/mangas/placeholder.svg'),(3,3,'uploads/mangas/placeholder.svg'),
(4,1,'uploads/mangas/placeholder.svg'),(4,2,'uploads/mangas/placeholder.svg'),(4,3,'uploads/mangas/placeholder.svg'),
(5,1,'uploads/mangas/placeholder.svg'),(5,2,'uploads/mangas/placeholder.svg'),(5,3,'uploads/mangas/placeholder.svg'),
(6,1,'uploads/mangas/placeholder.svg'),(6,2,'uploads/mangas/placeholder.svg'),(6,3,'uploads/mangas/placeholder.svg'),
(7,1,'uploads/mangas/placeholder.svg'),(7,2,'uploads/mangas/placeholder.svg'),(7,3,'uploads/mangas/placeholder.svg'),
(8,1,'uploads/mangas/placeholder.svg'),(8,2,'uploads/mangas/placeholder.svg'),(8,3,'uploads/mangas/placeholder.svg'),
(9,1,'uploads/mangas/placeholder.svg'),(9,2,'uploads/mangas/placeholder.svg'),(9,3,'uploads/mangas/placeholder.svg'),
(10,1,'uploads/mangas/placeholder.svg'),(10,2,'uploads/mangas/placeholder.svg'),(10,3,'uploads/mangas/placeholder.svg'),
(11,1,'uploads/mangas/placeholder.svg'),(11,2,'uploads/mangas/placeholder.svg'),(11,3,'uploads/mangas/placeholder.svg'),
(12,1,'uploads/mangas/placeholder.svg'),(12,2,'uploads/mangas/placeholder.svg'),(12,3,'uploads/mangas/placeholder.svg'),
(13,1,'uploads/mangas/placeholder.svg'),(13,2,'uploads/mangas/placeholder.svg'),(13,3,'uploads/mangas/placeholder.svg'),
(14,1,'uploads/mangas/placeholder.svg'),(14,2,'uploads/mangas/placeholder.svg'),(14,3,'uploads/mangas/placeholder.svg'),
(15,1,'uploads/mangas/placeholder.svg'),(15,2,'uploads/mangas/placeholder.svg'),(15,3,'uploads/mangas/placeholder.svg');