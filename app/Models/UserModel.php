<?php

class UserModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // Zkontroluje, zda uživatel už existuje
    public function userExists($username, $email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :user OR email = :email");
        $stmt->execute(['user' => $username, 'email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    // Registrace nového uživatele
    public function register($username, $email, $password, $role = 'author') {
        // 1. Zahasujeme heslo (BEZPEČNOSTNÍ NUTNOST!)
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // 2. Vložíme do DB
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (:user, :email, :pass, :role)
        ");

        return $stmt->execute([
            'user'  => $username,
            'email' => $email,
            'pass'  => $hash,
            'role'  => $role
        ]);
    }

    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :user");
        $stmt->execute(['user' => $username]);
        // Vrací asociativní pole (id, password, role...) nebo false, pokud neexistuje
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Změna role uživatele
    public function updateRole($id, $newRole) {
        $validRoles = ['admin', 'author', 'reviewer'];
        if (!in_array($newRole, $validRoles)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        return $stmt->execute(['role' => $newRole, 'id' => $id]);
    }

    // Získá uživatele, kteří mají roli 'reviewer'
    public function getReviewers() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'reviewer'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}