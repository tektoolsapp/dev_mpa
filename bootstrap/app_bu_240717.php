<?php

session_start();

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

//$user = new \App\Models\User;

//var_dump($user);

//die();

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

/*
 * 'streamOptions' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]
 */

//unset($app->getContainer()['errorHandler']);
//unset($app->getContainer()['phpErrorHandler']);

/*
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c['response']->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

*/

/*
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c['response']->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrongX!'. $exception);
    };
};

*/

/*
$container['customError'] = function($c){
    return function ($request, $response) {
        $output = ['success'=>0, 'error'=>"Custom Error Output."];
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($output));
    };
};

*/

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {

    return $capsule;

};

/*
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $data = [
            'message' => "Syntax error",
            'exception' => $exception
        ];
        return $c['response']
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data));
    };
};


set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting, so ignore it
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

*/

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

/*
$container[App\DashAction::class] = function ( $c ) {
    return new App\DashAction( $c );
};
*/

/*
$container[App\Support\Storage\SessionStorage::class] = function($container) {
    return new \App\Support\Storage\SessionStorage($container);

};

$container[App\Models\Product::class] = function($container){
    return new \App\Models\Product($container);
};

$container[App\Basket\Basket::class] = function ($container) {
    return new \App\Basket\Basket(

        $container->SessionStorage,

        $container->Product

    );
};

*/

$container['SessionStorage'] = function ($container) {
    return new \App\Support\Storage\SessionStorage($container->get('SessionStorage'));
};

//$container['Basket'] = function ($container) {
    //return new \App\Basket\Basket(storage: \App\Support\Storage\Contracts\StorageInterface, product: \App\Models\Product);/
//};

/*
$container[App\Basket\Basket::class] = function ($container) {
    return new App\Basket\Basket($container);
};

*/

$container["StorageInterface"] = function($container) {
    $storage_session = new \App\Support\Storage\SessionStorage('cart');
    return $storage_session;
};

$container['Product'] = function ($container) {
    return new \App\Models\Product(
        $container->get('Product')
    );
};

$container["Basket"] = function($container) {
    $basket = new App\Basket\Basket(
        $container->get(StorageInterface::class),
        $container->get(Product::class)
    );
    return $basket;
};


$container['members'] = function($container) {

    return new \App\Members\Members;

};

$container['flash'] = function($container) {

    return new \Slim\Flash\Messages;

};

$container['view'] = function($container) {

    /*
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [

        'cache' => false
        //'debug' => true

    ]);

    */

    $view = Factory::getEngine();

    $view->addExtension(new \Slim\Views\TwigExtension(

        $container->router,
        $container->request->getUri()
        //$container->dump

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

    //return new \Slim\Csrf\Guard;

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

    return new \App\Controllers\Products\CartController(

        $container
        //$container->SessionStorage,
        //$container->Product

    );

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