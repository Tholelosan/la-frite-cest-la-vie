<?php

// On require Autoload qui gère tous les composants (c'est le front controller du dossier vendor)
require_once __DIR__ .'./../vendor/autoload.php';

// Require nos classes
// 1- Models
require_once __DIR__.'./../app/Models/CoreModel.php';

// 2- Data
require_once __DIR__.'/../app/Utils/DBData.php';

// 3- Controllers
require_once __DIR__.'/../app/Controllers/CoreController.php';
require_once __DIR__.'/../app/Controllers/MainController.php';

// Les routes

$router = new AltoRouter();
// $router->setBasePath(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = isset($_SERVER['BASE_URI']) ? trim($_SERVER['BASE_URI']) : '/';
$router->setBasePath($baseUrl);

// Méthode GET ou POST | Chemin | Controller/Méthode | Desciption |
$router->map('GET', '/', ['MainController', 'home'], 'home');


$match = $router->match();
dump($match);

// Si pas de chemin ($match === false) > Alors on le renvoi vers le controleur et sa méthode qui gère PLS
// Si chemin ($match === [] > qui contient ce qu'on lui a dit en 3e param de la méthode map, ET au cas ou, les params des URL variables)

if ($match){
    $controllerQuOnVeut = $match['target'][0];
    $methodeQuOnVeut = $match['target'][1];  
    $controller = new $controllerQuOnVeut($baseUrl);
    $controller->$methodeQuOnVeut();
} else {
    $controller = new MainController($baseUrl);
    $controller->notFound();

}



// dump($match);
// array:3 [▼
//   "target" => array:2 [▼
//   0 => "MainController"
//   1 => "home"
// ]
// "params" => [
//     'toto' => '24512735140645'
// ]
// "name" => "home"
// ]

    
//RewriteEngine on
//RewriteCond %{REQUEST_FILENAME} !-f
//RewriteRule . index.php [L]

/**  1) ouvrir la balise
 *   2) Faire des commentaires
 *   3) Ecrire du PHP
 *   4) on instancie altorouter ?  pis on fait les routes ? ui :D
 *   5) require __DIR__.'/../vendor/autoload.php
 *   6) require les controllers ?
 * 
 */ 