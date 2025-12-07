<?php
require_once 'Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/ArticleModel.php';

class HomeController extends Controller {
    
    public function index() {
        $db = (new Database())->getConnection();
        $articleModel = new ArticleModel($db);

        // Zde je ta logika viditelnosti:
        // Na domovské stránce chceme ukazovat jen schválené články všem.
        // Admin má svůj speciální přehled v Admin sekci.
        $articles = $articleModel->getPublished();

        $data = [
            'articles' => $articles,
            'title' => 'Sborník konference'
        ];

        $this->view('home', $data);
    }
}