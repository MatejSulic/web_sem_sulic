<?php
require_once 'Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController extends Controller {

    public function register() {
        $data = []; // Data pro pohled (chybové hlášky atd.)

        // Pokud uživatel odeslal formulář (metoda POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Získání dat z formuláře
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            // 2. Validace (jsou hesla stejná?)
            if ($password !== $passwordConfirm) {
                $data['error'] = "Hesla se neshodují!";
            } else {
                // 3. Volání modelu
                $db = (new Database())->getConnection();
                $userModel = new UserModel($db);

                if ($userModel->userExists($username, $email)) {
                    $data['error'] = "Uživatel s tímto jménem nebo emailem už existuje.";
                } else {
                    // 4. Vytvoření uživatele
                    if ($userModel->register($username, $email, $password)) {
                        // Úspěch -> přesměrujeme na login (zatím na home)
                        header("Location: index.php?page=login&success=1");
                        exit;
                    } else {
                        $data['error'] = "Chyba při ukládání do databáze.";
                    }
                }
            }
        }

        // Zobrazení formuláře (předáme tam případné chyby)
        $this->view('register', $data);
    }
    public function login() {
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $db = (new Database())->getConnection();
            $userModel = new UserModel($db);

            // 1. Najdeme uživatele v DB
            $user = $userModel->getByUsername($username);

            // 2. Ověříme, zda existuje a zda sedí heslo (pomocí password_verify)
            if ($user && password_verify($password, $user['password'])) {
                
                // 3. BINGO! Uložíme údaje do Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Důležité pro práva (admin/author)

                // Přesměrujeme na úvod
                header("Location: index.php?page=home");
                exit;
            } else {
                $data['error'] = "Špatné jméno nebo heslo.";
            }
        }

        $this->view('login', $data);
    }

    public function logout() {
        // Zrušíme session
        session_destroy();
        // Přesměrujeme na login
        header("Location: index.php?page=login");
        exit;
    }

}