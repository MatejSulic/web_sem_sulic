<?php
require_once 'Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/ArticleModel.php';
require_once __DIR__ . '/../Models/ReviewModel.php';

class AdminController extends Controller {

    // Pomocná metoda pro kontrolu práv
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            die("Přístup odepřen. Nejste administrátor.");
        }
    }

    // Výpis uživatelů
    public function users() {
        $this->checkAdmin(); // Bezpečnostní závora

        $db = (new Database())->getConnection();
        $userModel = new UserModel($db);
        
        $users = $userModel->getAll();

        // Pokud jsme zrovna někoho upravili, zobrazíme zprávu
        $msg = isset($_GET['msg']) ? $_GET['msg'] : null;

        $this->view('admin_users', ['users' => $users, 'msg' => $msg]);
    }

    // Akce pro změnu role
    public function changeRole() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newRole = $_POST['new_role'];

            $db = (new Database())->getConnection();
            $userModel = new UserModel($db);
            
            // Ochrana: Admin nesmí změnit roli sám sobě (aby se nezamkl)
            if ($userId == $_SESSION['user_id']) {
                header("Location: index.php?page=admin-users&msg=error_self");
                exit;
            }

            $userModel->updateRole($userId, $newRole);
            header("Location: index.php?page=admin-users&msg=updated");
            exit;
        }
    }
    public function assignments() {
        $this->checkAdmin();

        $db = (new Database())->getConnection();
        $articleModel = new ArticleModel($db);
        $userModel = new UserModel($db);
        $reviewModel = new ReviewModel($db);

        // 1. Načteme všechny články
        $articles = $articleModel->getAll(); // Zde by bylo fajn filtrovat jen 'pending', ale nechme všechny
        
        // 2. Načteme seznam dostupných recenzentů (pro výběr v roletce)
        $availableReviewers = $userModel->getReviewers();

        // 3. Ke každému článku "přilepíme" informaci, kdo už ho hodnotí
        // (Tohle není úplně čisté OOP, ale pro jednoduchost to stačí)
        foreach ($articles as $article) {
            $article->assigned_reviewers = $reviewModel->getReviewersForArticle($article->id);
        }

        $this->view('admin_assignments', [
            'articles' => $articles, 
            'reviewers' => $availableReviewers
        ]);
    }

    // Zpracuje formulář pro přidání recenzenta
    public function assignReviewer() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = $_POST['article_id'];
            $reviewerId = $_POST['reviewer_id'];

            $db = (new Database())->getConnection();
            $reviewModel = new ReviewModel($db);

            $reviewModel->assignReviewer($articleId, $reviewerId);
            
            header("Location: index.php?page=admin-assignments&msg=assigned");
            exit;
        }
    }

    // Detail článku pro admina (zobrazení recenzí a rozhodnutí)
    public function articleDetail() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? null;
        if (!$id) die("Chybí ID");

        $db = (new Database())->getConnection();
        $articleModel = new ArticleModel($db);
        $reviewModel = new ReviewModel($db);

        // Načteme článek
        $article = $articleModel->getById($id);
        
        // Načteme k němu recenze
        $reviews = $reviewModel->getReviewsByArticleId($id);

        // Spočítáme průměrné hodnocení (jen pro zajímavost)
        $averageScore = 0;
        if (count($reviews) > 0) {
            $total = 0;
            foreach ($reviews as $r) {
                // Průměr ze 3 kritérií pro jednu recenzi
                $reviewAvg = ($r->score_technical + $r->score_content + $r->score_language) / 3;
                $total += $reviewAvg;
            }
            $averageScore = $total / count($reviews);
        }

        $this->view('admin_article_detail', [
            'article' => $article,
            'reviews' => $reviews,
            'average' => $averageScore
        ]);
    }

    // Akce pro změnu stavu (Schválit/Zamítnout)
    public function changeStatus() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['article_id'];
            $status = $_POST['status']; // 'accepted' nebo 'rejected'

            $db = (new Database())->getConnection();
            $articleModel = new ArticleModel($db);
            
            $articleModel->updateStatus($id, $status);
            
            header("Location: index.php?page=admin-articles");
            exit;
        }
    }

    public function manageArticles() {
        $this->checkAdmin();

        $db = (new Database())->getConnection();
        $articleModel = new ArticleModel($db);
        $userModel = new UserModel($db);
        $reviewModel = new ReviewModel($db);

        // 1. Načteme VŠECHNY články (seřazené od nejnovějších)
        $articles = $articleModel->getAll(); 
        
        // 2. Načteme seznam recenzentů (pro roletku)
        $reviewers = $userModel->getReviewers();

        // 3. Ke každému článku připojíme jeho recenzenty a spočítáme statistiky
        foreach ($articles as $article) {
            $article->reviews = $reviewModel->getReviewsByArticleId($article->id);
            
            // Spočítáme, kolik recenzí je hotových (má skóre > 0)
            $finishedCount = 0;
            foreach ($article->reviews as $r) {
                if ($r->score_technical > 0) $finishedCount++;
            }
            $article->finished_reviews = $finishedCount;
        }

        $this->view('admin_articles_dashboard', [
            'articles' => $articles, 
            'reviewers' => $reviewers
        ]);
    }
}