<?php
require_once 'Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/ArticleModel.php';

class ArticleController extends Controller {

    // Seznam všech článků
    public function index() {
        $db = (new Database())->getConnection();
        $articleModel = new ArticleModel($db);
        
        $articles = $articleModel->getAll();
        
        $this->view('articles_list', ['articles' => $articles]);
    }

    // Vytvoření článku
    public function create() {
        // 1. Ověření: Je uživatel přihlášen?
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }

        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Získání textových dat
            $title = $_POST['title'];
            $abstract = $_POST['abstract'];
            $authorId = $_SESSION['user_id']; // ID bereme ze session, ne z formuláře!

            // ZPRACOVÁNÍ SOUBORU (Upload)
            // $_FILES['pdf_file'] obsahuje info o nahraném souboru
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                
                $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
                $fileName = $_FILES['pdf_file']['name'];
                $fileType = $_FILES['pdf_file']['type'];
                
                // Jednoduchá validace koncovky (v reálu kontroluj i MIME type)
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if ($fileExtension === 'pdf') {
                    // Generujeme unikátní název, aby se soubory nepřepsaly
                    $newFileName = uniqid() . '.pdf';
                    
                    // Cesta kam nahrát (složka uploads musí existovat a mít práva!)
                    $uploadFileDir = __DIR__ . '/../../uploads/';
                    $dest_path = $uploadFileDir . $newFileName;

                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        // Soubor je na disku, teď uložíme záznam do DB
                        $db = (new Database())->getConnection();
                        $model = new ArticleModel($db);
                        
                        if ($model->create($title, $abstract, $newFileName, $authorId)) {
                            header("Location: index.php?page=articles"); // Přesměrování na seznam
                            exit;
                        } else {
                            $data['error'] = "Chyba při ukládání do DB.";
                        }
                    } else {
                        $data['error'] = "Chyba při přesunu souboru. Zkontroluj práva složky uploads!";
                    }
                } else {
                    $data['error'] = "Povoleny jsou pouze PDF soubory.";
                }
            } else {
                $data['error'] = "Musíte vybrat soubor.";
            }
        }

        $this->view('article_create', $data);
    }
    // ... existující kód ...

    // Zobrazí jen články přihlášeného uživatele
    public function myArticles() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }

        $db = (new Database())->getConnection();
        $model = new ArticleModel($db);
        
        $myArticles = $model->getByAuthorId($_SESSION['user_id']);
        
        $this->view('my_articles', ['articles' => $myArticles]);
    }

    // Smazání článku
    public function delete() {
        // ID článku pošleme přes URL (index.php?page=article-delete&id=5)
        $id = $_GET['id'] ?? null;
        
        if (!$id || !isset($_SESSION['user_id'])) {
            die("Přístup odepřen.");
        }

        $db = (new Database())->getConnection();
        $model = new ArticleModel($db);
        $article = $model->getById($id);

        // BEZPEČNOSTNÍ KONTROLA:
        // 1. Existuje článek?
        // 2. Je přihlášený uživatel autorem? (nebo je to admin)
        // 3. Je článek ve stavu 'pending'? (Schválené mazat nelze)
        if ($article && $article->author_id == $_SESSION['user_id'] && $article->status == 'pending') {
            
            // 1. Smazat soubor z disku (úklid)
            $filePath = __DIR__ . '/../../uploads/' . $article->filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // 2. Smazat záznam z DB
            $model->delete($id);
            
            header("Location: index.php?page=my-articles&msg=deleted");
            exit;
        } else {
            die("Nemáte oprávnění smazat tento článek nebo již byl publikován.");
        }
    }

    // Editace článku
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) die("Chybí ID");

        $db = (new Database())->getConnection();
        $model = new ArticleModel($db);
        $article = $model->getById($id);

        // Bezpečnostní kontrola vlastnictví a stavu
        if (!$article || $article->author_id != $_SESSION['user_id'] || $article->status != 'pending') {
            die("Nelze editovat.");
        }

        // Zpracování formuláře
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $abstract = $_POST['abstract'];
            
            $model->update($id, $title, $abstract);
            header("Location: index.php?page=my-articles&msg=updated");
            exit;
        }

        $this->view('article_edit', ['article' => $article]);
    }
}