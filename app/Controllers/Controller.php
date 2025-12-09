<?php

// Základní kontroler, který poskytuje společné funkce pro všechny ostatní kontrolery   

class Controller {
    public function view($viewName, $data = []) {
      
        extract($data);

        require __DIR__ . '/../Views/layouts/header.phtml';

        $viewPath = __DIR__ . "/../Views/$viewName.phtml";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<div class='alert alert-danger'>Chyba: Pohled $viewName neexistuje.</div>";
        }

        
  
        require __DIR__ . '/../Views/layouts/footer.phtml';
    }
}