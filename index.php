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

    case 'login':
        // Tady později přidáme AuthController
        echo "Tady bude přihlašování";
        break;

    default:
        // Stránka neexistuje (404)
        http_response_code(404);
        echo "<h1>404 - Stránka nenalezena</h1>";
        break;
}
?>