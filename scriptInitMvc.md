# Créer une architecture MVC en 5min

## Script à copier coller dans le terminal pour créer la base d'une architecture MVC

* __Depuis l'emplacement dans lequel on veut créer le repo__
```
mkdir app public
mkdir app/Models app/data app/views app/Controllers app/Utils public/assets
mkdir public/assets/css public/assets/img public/assets/js
touch .gitgnore app/.htaccess public/.htaccess app/config.dist.conf app/config.conf app/Controllers/CoreController app/data/.gitkeep app/Models/CoreModel app/views/home.php app/Utils/DBData.php public/index.php public/assets/css/style.css public/assets/img/.gitkeep public/assets/js/.gitkeep README.md
composer require altorouter/altorouter
composer require symfony/var-dumper
```

## Editer les fichiers .htaccess

* Dans `app/`

```
Deny From All
```

* Dans `public/`
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

## Le .gitignore
* Dans `'/'`
```
RawBlameHistory
  
/vendor/
composer.lock
app/config.conf
*.bak
```

## Editer le fichier config.dist.conf
* Dans `app/`
```
# Fichier de connexion générique à la BDD
RawBlameHistory
  
; config file

; database
DB_HOST=
DB_USERNAME=
DB_PASSWORD=
DB_NAME=
```
> __Ne pas oublier de le copier et renseigner les identifiants de connexion !__

## Générer le code de base du Front-Controller

* Pour le minimum syndicale dans `index.php`
```
// Autoload
require_once __DIR__ .'./../vendor/autoload.php';

// Require nos classes
// 1- Models
require_once __DIR__.'./../app/Models/CoreModel.php';

// 2- Data
require_once __DIR__.'/../app/Utils/DBData.php';

// 3- Controllers
require_once __DIR__.'/../app/Controllers/CoreController.php';
require_once __DIR__.'/../app/Controllers/MainController.php';

// Router
// 1- Base URL
$baseUrl = isset($_SERVER['BASE_URI']) ? trim($_SERVER['BASE_URI']) : '/';
$router->setBasePath($baseUrl);

// 2- Routes
$router->map('GET', '/', ['MainController', 'home'], 'home');

// 3- Dispatch
$match = $router->match();
if ($match){
    $controller = $match['target'][0];
    $method = $match['target'][1];  
    $controller = new $controller($baseUrl);
    $controller->$method();
} else {
    $controller = new MainController($baseUrl);
    $controller->notFound();
}
```

## Se connecter à la BDD
* Dans `app/Utils/DBData`
```
<?php
class DBData {
    private $dbh;
    public function __construct() {
        $configData = parse_ini_file(__DIR__.'/../config.conf');
        try {
            $this->dbh = new PDO(
                "mysql:host={$configData['DB_HOST']};dbname={$configData['DB_NAME']};charset=utf8",
                $configData['DB_USERNAME'],
                $configData['DB_PASSWORD'],
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
            );
        }
        
        catch(\Exception $exception) {
            echo 'Erreur de connexion...<br>';
            echo $exception->getMessage().'<br>';
            echo '<pre>';
            echo $exception->getTraceAsString();
            echo '</pre>';
            exit;
        }
    }
}
```

## Le CoreController
* Dans `app/Controllers/CoreController`
```
<?php
abstract class CoreController {
    protected $dbh;
    protected $baseUrl;
    public function __construct($baseUrl){
        $this->dbh = new DBData();
        $this->baseUrl = $baseUrl; 
    }
    
    protected function show($viewName, $data = [], $viewVars=[]) {
        require_once __DIR__.'./../views/header.php';
        require_once __DIR__.'./../views/'.$viewName.'.php';
        require_once __DIR__.'./../views/footer.php';
    }
}
```