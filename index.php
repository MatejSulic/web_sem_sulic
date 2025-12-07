<?php
session_start();

// Debugování (smaž při odevzdání)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'home':
        require_once 'app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
    

    case 'register':
        require_once 'app/Controllers/AuthController.php';
        (new AuthController())->register();
        break;

    case 'login':
        require_once 'app/Controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'logout':
        require_once 'app/Controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    case 'articles':
        require_once 'app/Controllers/ArticleController.php';
        (new ArticleController())->index();
        break;

    case 'article-create':
        require_once 'app/Controllers/ArticleController.php';
        (new ArticleController())->create();
        break;

    case 'my-articles':
        require_once 'app/Controllers/ArticleController.php';
        (new ArticleController())->myArticles();
        break;

    case 'article-delete':
        require_once 'app/Controllers/ArticleController.php';
        (new ArticleController())->delete();
        break;

    case 'article-edit':
        require_once 'app/Controllers/ArticleController.php';
        (new ArticleController())->edit();
        break;

    case 'admin-users':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->users();
        break;

    case 'admin-change-role':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->changeRole();
        break;

    case 'admin-assignments':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->assignments();
        break;

    case 'admin-assign':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->assignReviewer();
        break;

    case 'reviews':
        require_once 'app/Controllers/ReviewController.php';
        (new ReviewController())->index();
        break;

    case 'review-edit':
        require_once 'app/Controllers/ReviewController.php';
        (new ReviewController())->edit();
        break;

    case 'admin-article-detail':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->articleDetail();
        break;

    case 'admin-status':
        require_once 'app/Controllers/AdminController.php';
        (new AdminController())->changeStatus();
        break;
    default:
        // Stránka neexistuje (404)
        http_response_code(404);
        echo "<h1>404 - Stránka nenalezena</h1>";
        break;
}
?>