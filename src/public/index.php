<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
use Phalcon\Escaper;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Cache;
use Phalcon\Logger;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once BASE_PATH . '/vendor/autoload.php';
// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();

/**
 * register namespace service
 */
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . '/components',
        'App\Listeners' => APP_PATH . '/listener',
    ]
);

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

/**
 * register logger for signup log
 */
$container->set(
    'signupLogger',
    function () {
        $adapter1 = new \Phalcon\Logger\Adapter\Stream(APP_PATH . '/storage/logs/signup.log');
        return new Logger(
            'messages',
            [
                'main' => $adapter1,
            ]
        );
    }
);


/**
 * register logger for login log
 */
$container->set(
    'loginLogger',
    function () {
        $adapter2 = new \Phalcon\Logger\Adapter\Stream(APP_PATH . '/storage/logs/login.log');
        return new Logger(
            'messages',
            [
                'main' => $adapter2,
            ]
        );
    }
);
/**
 * register config
 */
$container->set(
    'config',
    function () {
        $file_name = '../app/components/config.php';
        $factory  = new ConfigFactory();
        return $factory->newInstance('php', $file_name);
    }
);
/**
 * register db service using config file
 */
$container->set(
    'db',
    function () {
        $db = $this->get('config')->db;
        return new Mysql(
            [
                'host'     => $db->host,
                'username' => $db->username,
                'password' => $db->password,
                'dbname'   => $db->dbname,
            ]
        );
    }
);
/**
 * register escaper class
 */
$container->set(
    'escaper',
    function () {
        return new Escaper();
    }
);

$container->set(
    'dateTime',
    function () {
        return new DateTimeImmutable();
    }
);
/**
 * register session service
 */
$container->setShared('session', function () {
    $session = new Manager();
    $files = new Stream([
        'savePath' => '/tmp',
    ]);
    $session->setAdapter($files)->start();
    return $session;
});
//Register cache service
$container->setShared(
    'cache',
    function () {
        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory($serializerFactory);

        $options = [
            'defaultSerializer' => 'Json',
            'lifetime'          => 7200
        ];

        $adapter = $adapterFactory->newInstance('apcu', $options);

        $cache = new Cache($adapter);
        return $cache;
    }
);
$container->set(
    'locale',
    (new App\Components\Locale())->getTranslator()
);

// Register the flash service with custom CSS classes
$container->set(
    'flash',
    function () {
        return new FlashDirect();
    }
);
// Register Event manager
$eventsManager = new EventsManager();
$eventsManager->attach(
    'notifications',
    new App\Listeners\NotificationsListener()
);
$application->setEventsManager($eventsManager);
$eventsManager->attach(
    'application:beforeHandleRequest',
    new App\Listeners\NotificationsListener()
);
$container->set(
    'EventsManager',
    $eventsManager
);


// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('phalt');
//     },
//     true
// );

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
