<?php

use App\App;
use Slim\Views\Twig;
use Slim\Csrf\Guard;
use App\Auth\Auth;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as v;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

//use App\Mpdf\Mpdf\Mpdf

use App\View\Factory;

session_start();

require __DIR__. '/../vendor/autoload.php';

//$mpdf = new \Mpdf\Mpdf();

LengthAwarePaginator::viewFactoryResolver(function() {
    return new Factory;
});

LengthAwarePaginator::defaultView('pagination/bootstrap.twig');

Paginator::currentPathResolver(function(){
    //dump($_SERVER['REQUEST_URI']);
    return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI']. '?') : '/';

});

Paginator::currentPageResolver(function(){

    if(isset($_GET['page'])){
        $current_page = $_GET['page'];
        $_SESSION['current_page'] = $current_page;
    } else {
        $current_page = $_SESSION['current_page'];
    }

    return isset($current_page) ? $current_page : 1;
});

try {
    (new Dotenv\Dotenv(__DIR__.'/'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "error";
}

$app = new App;

$container = $app->getContainer();

$capsule = new Capsule;
/*
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'codecourse',
    'username' => 'root',
    'password' => 'tIAQKuzkAX4wsg0j',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
]);
*/

$capsule->addConnection($container->get('db'));

$capsule->setAsGlobal();
$capsule->bootEloquent();

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('37qp5hs5cz66fmx9');
Braintree_Configuration::publicKey('d7xdcbx5xtfmnvrt');
Braintree_Configuration::privateKey('e45aef09111107f84845009934aabff4');

require __DIR__. '/../app/routes.php';

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container->get(Twig::class)));
$app->add(new \App\Middleware\OldInputMiddleware($container->get(Twig::class)));
$app->add(new \App\Middleware\CsrfViewMiddleware($container->get(Twig::class), $container->get(Guard::class)));

v::with('App\\Validation\\Rules\\');


/*

//use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Message\ServerRequestInterface as Request;

use Respect\Validation\Validator as v;
use App\View\Factory;

use App\Mail\Mailer\Mailer;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;


require __DIR__. '/../vendor/autoload.php';


LengthAwarePaginator::viewFactoryResolver(function() {

   return new Factory;

});

LengthAwarePaginator::defaultView('pagination/bootstrap.twig');

Paginator::currentPathResolver(function(){

    //dump($_SERVER['REQUEST_URI']);

    return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI']. '?') : '/';

});

Paginator::currentPageResolver(function(){

    if(isset($_GET['page'])){

        $current_page = $_GET['page'];
        $_SESSION['current_page'] = $current_page;

    } else {
        $current_page = $_SESSION['current_page'];
    }

    return isset($current_page) ? $current_page : 1;

});

$app = new \Slim\App([

    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'codecourse',
            'username' => 'root',
            'password' => 'tIAQKuzkAX4wsg0j',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ],
        'mail' => [
            'host' => 'smtp.mailtrap.io',
            'port' => '2525',
            'from' => [
                'name' => 'Allan Hyde',
                'address' => 'allan.hyde@tektools.com.au'
            ],
            'username' => 'cecb9045317090',
            'password' => 'cf36d1359675e5'
        ]
    ]

]);


$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {

    return $capsule;

};

$container['mail'] = function ($container) {

    $config = $container->settings['mail'];

    $transport = Swift_SmtpTransport::newInstance($config['host'], $config['port'])

        ->setUsername($config['username'])
        ->setPassword($config['password']);

    $swift = Swift_Mailer::newInstance($transport);

    return (new App\Mail\Mailer\Mailer($swift, $container->view))

        ->alwaysFrom($config['from']['address'], $config['from']['name']);

};

$container['logger'] = function($container) {

    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log');
    $logger->pushHandler($file_handler);

    return $logger;
};

$container['auth'] = function($container) {
    return new \App\Auth\Auth;

};


$container['members'] = function($container) {

    return new \App\Members\Members;

};

$container['flash'] = function($container) {

    return new \Slim\Flash\Messages;

};

$container['view'] = function($container) {

    $view = Factory::getEngine();

    $view->addExtension(new \Slim\Views\TwigExtension(

        $container->router,
        $container->request->getUri()

    ));

    $random = mt_rand(100000, 999999);

    $view->getEnvironment()->addGlobal('inc', $random);

    $view->getEnvironment()->addGlobal('auth', [

        'check' => $container->auth->check(),
        'user' => $container->auth->user()

    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;

};

$container['validator'] = function($container) {

    return new App\Validation\Validator;

};

$container['HomeController'] = function($container) {

    return new \App\Controllers\HomeController($container);

};

$container['TestController'] = function($container) {

    return new \App\Controllers\TestController($container);

};

$container['AuthController'] = function($container) {

    return new \App\Controllers\Auth\AuthController($container);

};

$container['PasswordController'] = function($container) {

    return new \App\Controllers\Auth\PasswordController($container);

};

$container['csrf'] = function($container) {

    $guard = new \Slim\Csrf\Guard;
    $guard->setPersistentTokenMode(true);
    return $guard;

};

$container['SettingsController'] = function($container) {

    return new \App\Controllers\Settings\SettingsController($container);

};

$container['ManageUsersController'] = function($container) {

    return new \App\Controllers\Settings\ManageUsersController($container);

};

$container['MembersController'] = function($container) {

    return new \App\Controllers\Members\MembersController($container);

};

$container['ContactsController'] = function($container) {

    return new \App\Controllers\Contacts\ContactsController($container);

};

$container['DirectoryController'] = function($container) {

    return new \App\Controllers\Members\DirectoryController($container);

};

$container['RenewalsController'] = function($container) {

    return new \App\Controllers\Members\RenewalsController($container);

};

$container['ProductController'] = function($container) {

    return new \App\Controllers\Products\ProductController($container);

};

$container['CartController'] = function($container) {

    return new \App\Controllers\Products\CartController($container);

};

$container['EventsController'] = function($container) {

    return new \App\Controllers\Events\EventsController($container);

};

$container['FlimsysController'] = function($container) {

    return new \App\Controllers\Flimsys\FlimsysController($container);

};

$container['ReportsController'] = function($container) {

    return new \App\Controllers\Reports\ReportsController($container);

};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

v::with('App\\Validation\\Rules\\');

require __DIR__. '/../app/routes.php';

*/