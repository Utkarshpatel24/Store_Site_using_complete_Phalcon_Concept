<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Events\Manager as EventManager;
//___FOR LOGS____
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as ls;
//____FOR ESCAPER____
use Phalcon\Escaper;
//____FOR CONFIG______
use Phalcon\Config\ConfigFactory;
use App\Components\Locale;

//______FOR JWT________
require_once"../vendor/autoload.php";


$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
        APP_PATH . "/listener/",
        
    ]
);
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH."/components"
    ]
);
$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);

//________CONTAINER FOR CONFIG___________
$container->set(
    'config',
    function() {
        $fileName = APP_PATH . '/storage/config/config.php';
        $factory  = new ConfigFactory();

        $config = $factory->newInstance('php', $fileName);
        return $config;
    }
);


$container->set(
    'db',
    function () {
        $config = $this->get('config');
        // print_r($config['db']['']);
        // die;
        return new Mysql(
            // $config['db']
            [
                'host'     => $config['db']['host'],
                'username' => $config['db']['username'],
                'password' => $config['db']['password'],
                'dbname'   => $config['db']['dbname'],
            ]
        );
    }
);


$container->set(
    'session', 
    function() {
        $sesion = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
            $sesion->setAdapter($files)->start();
            return $sesion;

    }
);



function getArray($user)
{
    return array(
        'user_id' => $user->user_id,
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
        'password' => $user->password,
        'role' => $user->role,
        'status' => $user->status
    );


}

$container->set(
    'cookies',
    function() {
        $cookies =new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    }
);




// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('phalt');
//     },
//     true
// );



//_____________EVENT MANAGER_______________

$eventManager = new EventManager();

// $eventManager->attach(
//     "main",
//     new EventListener()
// );

$eventManager->attach(
    "application:beforeHandleRequest",
    new EventListener() 
);
$application->setEventsManager($eventManager);
$container->set(
    "eventManager",
    function() use ($eventManager) {
        return $eventManager;
    }
);


//___________LOGS________

$container->set(
    'loginLog',
    function() {
        $adapter = new ls(APP_PATH . '/storage/logs/login.log');
        $logger  = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );
        return $logger;
    }
);

$container->set(
    'signupLog',
    function() {
        $adapter = new ls(APP_PATH . '/storage/logs/signup.log');
        $logger = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );
        return $logger;
    }
);

//_________ESCAPER_______
$container->set(
    'escaper',
    function() {
        $escaper = new Escaper();
        return $escaper;
    }
);

//_________TRANSLATOR________
$container->set('locale', (new Locale())->getTranslator());

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
