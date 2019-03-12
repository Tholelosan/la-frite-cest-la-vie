<?php


class MainController extends CoreController {

    // public function __construct() {
    //     parent::__construct();
    //     $this->name = 'TATA';
    // }
    public function home(){
        $this->show('home');            
    }


    public function notFound(){
        // header('HTTP/1.0 404 Not Found');
        $this->show('notFound');
    }

    
}