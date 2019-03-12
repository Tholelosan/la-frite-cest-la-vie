<?php

abstract class CoreController {

    protected $dbd;
    protected $baseUrl;

    public function __construct($baseUrl){
        //au moment où on instancie la classe, ça connecte direct à la BDD, coolos
        // $this->dbd = new DBData();
        $this->baseUrl = $baseUrl; 
    }
    

    protected function show($viewName, $viewVars=[]) {

        require_once __DIR__.'./../views/header.tpl.php';
        require_once __DIR__.'./../views/'.$viewName.'.tpl.php';
        require_once __DIR__.'./../views/footer.tpl.php';        
    }

}