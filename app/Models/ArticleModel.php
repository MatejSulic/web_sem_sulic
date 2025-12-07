<?php

class ArticleModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // Vloží nový článek
    public function create($title, $abstract, $filename, $authorId) {
        $stmt = $this->db->prepare("
            INSERT INTO articles (title, abstract, filename, author_id, status) 
            VALUES (:title, :abstract, :filename, :author_id, 'pending')
        ");
        
        return $stmt->execute([
            'title'     => $title,
            'abstract'  => $abstract,
            'filename'  => $filename,
            'author_id' => $authorId
        ]);
    }

    // Získá všechny články i se jmény autorů
    public function getAll() {
        // JOIN spojí tabulku článků s tabulkou uživatelů, abychom znali jméno autora
        $query = "
            SELECT articles.*, users.username as author_name 
            FROM articles 
            JOIN users ON articles.author_id = users.id 
            ORDER BY articles.created_at DESC
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 1. Získání článků jen pro konkrétního autora (pro sekci "Moje články")
    public function getByAuthorId($authorId) {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE author_id = :aid ORDER BY created_at DESC");
        $stmt->execute(['aid' => $authorId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // 2. Získání jednoho článku podle ID (pro Editaci a Mazání)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // 3. Smazání článku
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // 4. Aktualizace článku (bez změny souboru)
    public function update($id, $title, $abstract) {
        $stmt = $this->db->prepare("UPDATE articles SET title = :title, abstract = :abstract WHERE id = :id");
        return $stmt->execute([
            'title' => $title,
            'abstract' => $abstract,
            'id' => $id
        ]);
    }

    // Změna stavu článku (accepted/rejected)
    public function updateStatus($id, $status) {
        $validStatuses = ['pending', 'accepted', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE articles SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}