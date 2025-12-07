CREATE DATABASE IF NOT EXISTS conference_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE conference_db;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('author','reviewer','admin') NOT NULL DEFAULT 'author',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    author_id INT NOT NULL,
    status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_users FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    score_technical INT NOT NULL DEFAULT 0,
    score_content INT NOT NULL DEFAULT 0,
    score_language INT NOT NULL DEFAULT 0,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_articles FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_users FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uq_article_reviewer UNIQUE (article_id, reviewer_id)
);

INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$12$BQX6stjoiNUBysxvgp5x9.8qKCoSumhbMjifZPqp26ZKurZ8bcy5S', 'admin'),
('reviewer1', 'reviewer1@example.com', '$2y$12$BQX6stjoiNUBysxvgp5x9.8qKCoSumhbMjifZPqp26ZKurZ8bcy5S', 'reviewer'),
('author1', 'author1@example.com', '$2y$12$BQX6stjoiNUBysxvgp5x9.8qKCoSumhbMjifZPqp26ZKurZ8bcy5S', 'author');

INSERT INTO articles (title, abstract, filename, author_id, status) VALUES
('Ukázkový článek', 'Krátký abstrakt demonstračního článku.', 'demo.pdf', 3, 'accepted');

INSERT INTO reviews (article_id, reviewer_id, score_technical, score_content, score_language, comment) VALUES
(1, 2, 8, 9, 7, 'Doporučuji k publikaci.');
