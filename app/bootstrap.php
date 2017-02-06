<?php

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR.'/app');
define('HOME', BASEDIR.'/public');
define('STORAGE', APP.'/storage');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', STORAGE.'/temp/');
define('VERSION', '7.0');

require_once BASEDIR.'/vendor/autoload.php';

/**
 * Регистрация классов
 */
$aliases = [
    'ORM' => 'Granada\ORM',
    'Capsule' => 'Illuminate\Database\Capsule\Manager',
];
AliasLoader::getInstance($aliases)->register();

if (! env('APP_ENV')) {
    $dotenv = new Dotenv\Dotenv(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG')) {
    $whoops = new Whoops\Run();
    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(function() {
        $_SERVER = array_except($_SERVER, array_keys($_ENV));
        $_ENV = [];
    });
    $whoops->register();
}

$capsule = new Illuminate\Database\Capsule\Manager();

$capsule->addConnection([
    'driver'    => env('DB_DRIVER'),
    'host'      => env('DB_HOST'),
    'database'  => env('DB_DATABASE'),
    'username'  => env('DB_USERNAME'),
    'password'  => env('DB_PASSWORD'),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

/*use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));*/
$capsule->setAsGlobal();
$capsule->bootEloquent();
$capsule::connection()->enableQueryLog();


ORM::configure([
    'connection_string' => env('DB_DRIVER').':host='.env('DB_HOST').';dbname='.env('DB_DATABASE').';port='.env('DB_PORT'),
    'username'       => env('DB_USERNAME'),
    'password'       => env('DB_PASSWORD'),
    'logging'        => env('APP_DEBUG'),
    'caching'        => true,
    'driver_options' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ],
]);
