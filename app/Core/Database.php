<?php
class Database {
    public function getConnection() {
        // Nastavení pro XAMPP/Linux
        $host = 'localhost';
        $db   = 'conference_db'; 
        $user = 'root';
        $pass = ''; 
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Chyba připojení k DB: " . $e->getMessage());
        }
    }
}