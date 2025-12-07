<?php

class ReviewModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // 1. Přiřadí recenzenta k článku (vytvoří prázdnou recenzi)
    public function assignReviewer($articleId, $reviewerId) {
        // Nejdřív zkontrolujeme, jestli už ho nehodnotí (aby tam nebyl 2x)
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE article_id = :aid AND reviewer_id = :rid");
        $stmt->execute(['aid' => $articleId, 'rid' => $reviewerId]);
        if ($stmt->fetch()) {
            return false; // Už je přiřazen
        }

        // Vložíme nový záznam (body jsou zatím NULL)
        $stmt = $this->db->prepare("INSERT INTO reviews (article_id, reviewer_id, score_technical, score_content, score_language) VALUES (:aid, :rid, 0, 0, 0)");
        return $stmt->execute(['aid' => $articleId, 'rid' => $reviewerId]);
    }

    // 2. Získá seznam recenzentů přiřazených k danému článku
    // Používáme JOIN, abychom viděli i jména recenzentů
    public function getReviewersForArticle($articleId) {
        $stmt = $this->db->prepare("
            SELECT u.username, u.id 
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.article_id = :aid
        ");
        $stmt->execute(['aid' => $articleId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 3. Získá seznam recenzí pro konkrétního recenzenta (jeho "To-Do" list)
    public function getByReviewerId($reviewerId) {
        $stmt = $this->db->prepare("
            SELECT r.*, a.title, a.filename, a.status as article_status
            FROM reviews r
            JOIN articles a ON r.article_id = a.id
            WHERE r.reviewer_id = :rid
        ");
        $stmt->execute(['rid' => $reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 4. Získá jednu recenzi podle ID (pro formulář)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // 5. Uložení hodnocení
    public function submitReview($id, $tech, $content, $lang, $comment) {
        $stmt = $this->db->prepare("
            UPDATE reviews 
            SET score_technical = :tech, 
                score_content = :cont, 
                score_language = :lang, 
                comment = :comm
            WHERE id = :id
        ");
        return $stmt->execute([
            'tech' => $tech,
            'cont' => $content,
            'lang' => $lang,
            'comm' => $comment,
            'id'   => $id
        ]);
    }

    // Získá všechny recenze pro daný článek (včetně jmen recenzentů)
    public function getReviewsByArticleId($articleId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username as reviewer_name 
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.article_id = :aid
        ");
        $stmt->execute(['aid' => $articleId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}