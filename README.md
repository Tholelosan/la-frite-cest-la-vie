# Petit exercice d'architecture
_Objectif : afficher 'Hello world !' sur une page en respectant l'architecture vue cette saison_

## Organisation de l'arborescence
```
.
├── app
│   ├── Controllers
│   │   ├── CoreController.php
│   │   └── MainController.php
│   ├── data
│   │   └── queries.sql
│   ├── Models
│   │   └── CoreModel.php
│   ├── Utils
│   │   └── DBData.php
│   └── views
│       ├── footer.tpl.php
│       ├── header.tpl.php
│       ├── home.tpl.php
│       └── notFound.tpl.php
├── composer.json
├── composer.lock
├── public
│   ├── assets
│   │   ├── css
│   │   ├── img
│   │   └── js
│   └── index.php
├── README.md
└── vendor
    ├── altorouter
    ├── autoload.php
    ├── composer
    └── symfony
        ├── polyfill-mbstring
        └── var-dumper


```
## Installation de composer et des plugs-in
### Pour ce mini projet on installe `var-dumper` et `AltoRouter`
> * https://packagist.org/packages/symfony/var-dumper
> * https://packagist.org/packages/altorouter/altorouter

### Le code a collé dans le terminal pour installer les dépendances
* Installation d'AltoRouter en ligne de commande à la racine du projet == `composer require altorouter/altorouter`
* Installation de var-dumper en ligne de commande à la racine du projet == `composer require symfony/var-dumper`

* Le fichier nécessaire à conserver lors du versioning est `composer.json`. Le reste n'est pas nécessaire car pourra être installé en local grâce à un `composer install`
* Pour ignorer le reste,  voir le fichier `.gitignore` situé à la racine du projet

## Le front-controller

* Le front controller est la porte d'entrée obligatoire de l'ensemble des requêtes du site, on peut donc :
    * require tous les fichiers nécessaire au fonctionnement ici
    * gérer le routing

### Les require

* On a d'abord besoin de l'autoload.php qui insèrent les dépendances installées automatiquement
* Ensuite les classes avec dans l'ordre : les Models, les Utils et les Controllers
```
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
```

### Les routes

#### .htaccess

* Les fichiers `.htaccess` donnent des infos à APACHE sur le comportement qu'il doit avoir avec le dossier où se trouve le fichier `.htaccess` et tous ses enfants

* On a besoin de deux fichiers `.htaccess`. L'un pour la réécriture d'URL, l'autre pour prévenir l'accès aux dossiers sensibles.

##### Prévenir l'accès au dossier `app`
* Contenu du fichier :
```
Deny From All
```

##### Réécriture d'URL

```
# Activer la réécriture d'URL
RewriteEngine On

# dynamically setup base URI (On utilise le nom de clé 'BASE_URI' qui se trouve dans la superglobale $_SERVER >> On aurait pu l'appeler 'tata')
RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
RewriteRule ^(.*) - [E=BASE_URI:%1]

# redirect every request to index.php
# and give the relative URL in "_url" GET param
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php?_url=/$1 [QSA,L]
```

#### Paramétrage du router
##### Il faut instancier le router == `$router = new AltoRouter();`
    * Ceci permet d'avoir un objet dans la variable `$router` qui contient toutes les méthodes et propriétés de notre outil `AltoRouter` qui est en fait une classe.
##### Définir l'URL de base grâce à la méthode `setBasePath` :
```
// $router->setBasePath(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = isset($_SERVER['BASE_URI']) ? trim($_SERVER['BASE_URI']) : '/';
$router->setBasePath($baseUrl);
```
##### Paramétrer nos routes grâce à la méthode `map` :
```
$router->map('GET', '/', ['MainController', 'home'], 'home');
```
* Ici la méthode `map()` prend en paramètres :
    * `'GET'` == Méthode HTTP
    * `'/'` == chemin à ajouter à l'URL de base pour parvenir à la page (ici la racine car il s'agit d'une page `'home'`)
    * `['MainController', 'home']` == Paramètres qui seront ensuite retrouvé dans la variable match qui correspondent au `Controller` qui gère la route et le nom de la méthode qui génère la `view`
    * `'home'` == Pas utile mais à renseigner pour l'information sur la page
##### Vérifier s'il y a match entre l'adresse dans le navigateur et la route définie

* `$match = $router->match();` == La méthode `match()` renvoie false si il n'y a pas de correspondance entre le chemin et l'URL rentrée dans le navigateur // Elle renvoie un tableau associatif dans le cas contraire :
```
dump($match);
array:3 [▼
  "target" => array:2 [▼
  0 => "MainController"
  1 => "home"
]
"params" => [
    'toto' => '24512735140645'
]
"name" => "home"
]
```
* La première clé du tableau (`'target'`) comprend le tableau donnée en 3e paramètre à la méthode `map()`
* La seconde clé du tableau (`'params'`) comprend un tableau associatif des éventuels paramètres donnés dans le chemin (notamment les chemin qui varient via le `/chemin/item/[i:id]`)
* La troisième est le nom donné en 4e paramètre de la méthode `map()`

##### Orienter vers le controller qui match OU le controller qui gère le 404
```
if ($match){
    $controllerQuOnVeut = $match['target'][0];
    $methodeQuOnVeut = $match['target'][1];  
    $controller = new $controllerQuOnVeut($baseUrl);
    $controller->$methodeQuOnVeut();
} else {
    $controller = new MainController($baseUrl);
    $controller->notFound();

}

```
* Si il y a un match, alors je veux récupérer le nom du `controller` dans mon tableau associatif de la variable `$match` et le nom de la méthode qui gère les views. Après j'instancie le `controller` et utilise la méthode.
* Sinon, j'instancie le `controller` qui gère la méthode qui génère la `view` de la 404

## Les controllers

* Pour notre projet on a pas besoin de 1000 controllers, on va juste créer un `CoreController` qui ne sera pas instancié (abstract) et un `MainController` qui gèrera les méthodes qui génèrent la page `'home'` et `'notFound'`
* Le `CoreController` == gère la connexion à la BDD (`DBData`) et la méthode `show()`
```
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
```
* `protected` == accessible pour les enfants de cette classe
> * L'instanciation de `DBData` est commentée pour ne pas créer d'erreurs à la génération de la vue

* Le `MainController` == gère la génération des vues `'home'` et `'notFound'`
```
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
```
* C'est l'enfant du `CoreController` donc il hérite de la connexion à la BDD et de la méthode `show()`, il peut donc les utiliser.
* La fonction `header` est commentée car on a eu une erreur sur l'ordre dans lequel la requête est générée >> __RTFM__

## DBData et connexion à la BDD
_Fabrice me force à venir jouer sur PUBG je ne vais donc pas finir ce README tout de suite :p SOORRYYY_

Vite fait : il faut créer la classe DBData pour y coller un try catch qui ira chercher les infos de connexion qu'elle prend en paramètre dans le fichier config.conf qu'il ne faut pas versionner (coucou .gitignore) car assez délicat niveau sécurité ! Par contre on crée une version générique de config.conf dans le dossier app afin de permettre la copie pour ce fichier conf (ou l'inverse tiens :p)