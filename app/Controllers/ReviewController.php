<?php
require_once 'Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/ReviewModel.php';

class ReviewController extends Controller {

    // Seznam úkolů pro recenzenta
    public function index() {
        // Kontrola role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reviewer') {
            die("Přístup odepřen.");
        }

        $db = (new Database())->getConnection();
        $model = new ReviewModel($db);
        
        $reviews = $model->getByReviewerId($_SESSION['user_id']);
        
        $this->view('reviews_list', ['reviews' => $reviews]);
    }

    // Formulář pro hodnocení
    public function edit() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reviewer') {
            die("Přístup odepřen.");
        }

        $reviewId = $_GET['id'] ?? null;
        $db = (new Database())->getConnection();
        $model = new ReviewModel($db);
        
        $review = $model->getById($reviewId);

        // BEZPEČNOST: Patří tato recenze přihlášenému uživateli?
        if (!$review || $review->reviewer_id != $_SESSION['user_id']) {
            die("Tuto recenzi nemůžete editovat.");
        }

        // Zpracování formuláře
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tech = $_POST['score_technical'];
            $cont = $_POST['score_content'];
            $lang = $_POST['score_language'];
            $comment = $_POST['comment'];

            $model->submitReview($reviewId, $tech, $cont, $lang, $comment);
            
            header("Location: index.php?page=reviews&msg=saved");
            exit;
        }

        $this->view('review_form', ['review' => $review]);
    }
}