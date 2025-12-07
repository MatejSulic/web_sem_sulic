<?php
// Musíme načíst rodiče
require_once 'Controller.php';

class HomeController extends Controller {
    
    public function index() {
        // Tady bychom normálně tahali data z databáze
        $data = [
            'welcome_message' => 'Vítejte v konferenčním systému'
        ];

        // Zavoláme metodu view z rodiče, která to obalí headerem a footerem
        $this->view('home', $data);
    }
}
?>