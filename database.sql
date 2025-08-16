-- Database: manga_reader

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mangas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  author VARCHAR(255) NOT NULL,
  description TEXT,
  cover_image VARCHAR(255) NOT NULL DEFAULT '',
  release_date DATE NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS genres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manga_genres (
  manga_id INT NOT NULL,
  genre_id INT NOT NULL,
  PRIMARY KEY (manga_id, genre_id),
  FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
  FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  manga_id INT NOT NULL,
  chapter_number VARCHAR(32) NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uniq_manga_chapter (manga_id, chapter_number),
  FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapter_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  page_number INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  UNIQUE KEY uniq_chapter_page (chapter_id, page_number),
  FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  email VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  is_replied TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookmarks (
  user_id INT NOT NULL,
  manga_id INT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, manga_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reading_history (
  user_id INT NOT NULL,
  manga_id INT NOT NULL,
  chapter_id INT NOT NULL,
  last_read_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, manga_id),
  INDEX (last_read_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
  FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Authors table (optional; mangas.author string still used for display; admin authors CRUD uses this table)
CREATE TABLE IF NOT EXISTS authors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample users (password: password)
INSERT INTO users (name, email, password_hash, role, created_at) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
('User One', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW());

-- Sample genres
INSERT INTO genres (name, slug) VALUES
('Action','action'),('Adventure','adventure'),('Comedy','comedy'),('Drama','drama'),('Fantasy','fantasy');

-- Sample mangas
INSERT INTO mangas (title, slug, author, description, cover_image, release_date, is_featured, created_at, updated_at) VALUES
('Crimson Blade','crimson-blade','A. Writer','A tale of swords and honor.','sample_crimson.jpg','2022-05-01',1,NOW(),NOW()),
('Skyward Quest','skyward-quest','B. Author','Explore the skies on a daring quest.','sample_skyward.jpg','2021-09-15',1,NOW(),NOW()),
('Mystic Echoes','mystic-echoes','C. Storyteller','Whispers of ancient magic.','sample_mystic.jpg','2020-12-10',0,NOW(),NOW()),
('Neon Run','neon-run','D. Novelist','A cyberpunk chase through neon streets.','sample_neon.jpg','2023-01-22',0,NOW(),NOW()),
('Harbor Lights','harbor-lights','E. Scribe','Drama unfolds in a coastal town.','sample_harbor.jpg','2019-07-07',0,NOW(),NOW());

-- Link genres
INSERT INTO manga_genres (manga_id, genre_id) VALUES
(1,1),(1,4),(2,2),(2,5),(3,5),(4,1),(4,3),(5,4);

-- Sample chapters
INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES
(1,'1','Dawn of Steel',NOW()),(1,'2','Crossing Blades',NOW()),(1,'3','Oathkeeper',NOW()),
(2,'1','First Flight',NOW()),(2,'2','Storm Above',NOW()),(2,'3','Skyfall',NOW()),
(3,'1','Awakening',NOW()),(3,'2','Echo Chamber',NOW()),(3,'3','Arcane Threads',NOW()),
(4,'1','Neon Streets',NOW()),(4,'2','Circuit Breaker',NOW()),(4,'3','Night Run',NOW()),
(5,'1','Harbor Dawn',NOW()),(5,'2','Low Tide',NOW()),(5,'3','Beacon',NOW());

-- Sample images (3 pages per chapter, placeholder images to be provided in uploads/mangas/)
-- For demo, point to sample placeholders; copy files named page_1.jpg, page_2.jpg, page_3.jpg into uploads/mangas/
INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES
(1,1,'page_1.jpg'),(1,2,'page_2.jpg'),(1,3,'page_3.jpg'),
(2,1,'page_1.jpg'),(2,2,'page_2.jpg'),(2,3,'page_3.jpg'),
(3,1,'page_1.jpg'),(3,2,'page_2.jpg'),(3,3,'page_3.jpg'),
(4,1,'page_1.jpg'),(4,2,'page_2.jpg'),(4,3,'page_3.jpg'),
(5,1,'page_1.jpg'),(5,2,'page_2.jpg'),(5,3,'page_3.jpg'),
(6,1,'page_1.jpg'),(6,2,'page_2.jpg'),(6,3,'page_3.jpg'),
(7,1,'page_1.jpg'),(7,2,'page_2.jpg'),(7,3,'page_3.jpg'),
(8,1,'page_1.jpg'),(8,2,'page_2.jpg'),(8,3,'page_3.jpg'),
(9,1,'page_1.jpg'),(9,2,'page_2.jpg'),(9,3,'page_3.jpg'),
(10,1,'page_1.jpg'),(10,2,'page_2.jpg'),(10,3,'page_3.jpg'),
(11,1,'page_1.jpg'),(11,2,'page_2.jpg'),(11,3,'page_3.jpg'),
(12,1,'page_1.jpg'),(12,2,'page_2.jpg'),(12,3,'page_3.jpg'),
(13,1,'page_1.jpg'),(13,2,'page_2.jpg'),(13,3,'page_3.jpg'),
(14,1,'page_1.jpg'),(14,2,'page_2.jpg'),(14,3,'page_3.jpg'),
(15,1,'page_1.jpg'),(15,2,'page_2.jpg'),(15,3,'page_3.jpg');