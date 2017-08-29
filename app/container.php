<?php

use function DI\get;
use App\Basket\Basket;
use App\MyOrders\ProcessOrders;
use Slim\Views\Twig;
use App\Auth\Auth;

use App\Test\TestClass;

use App\Models\Members;
use App\Models\MemberTypes;
use App\Models\SpecialistSkills;
use App\Models\Contacts;
use App\Models\Directory;
use App\Models\MpaUser;
use App\Models\UserStatus;
use App\Models\Invoices;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Asset;
use Slim\Views\TwigExtension;
use Interop\Container\ContainerInterface;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Validator;
use Slim\Flash\Messages as Flash;
use App\Mail\Mailer\Mailer;
use Slim\Csrf\Guard;
use App\Support\Storage\Contracts\StorageInterface;
use App\Support\Storage\SessionStorage;
use App\View\Factory;
use \DrewM\MailChimp\MailChimp;

return [
    'db' => [
        // Eloquent configuration
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'mpa',
        'username' => 'root',
        'password' => 'tIAQKuzkAX4wsg0j',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => ''
    ],

    'router' => get(Slim\Router::class),

    StorageInterface::class => function (ContainerInterface $c) {
        //return new SessionStorage('cart');
        return new SessionStorage();
    },

    ValidatorInterface::class => function (ContainerInterface $c) {
        return new Validator();
    },
    Twig::class => function (ContainerInterface $c) {
        /*
        $twig = new Twig(__DIR__ . '/../resources/views', [
            'cache' => false
        ]);
        */
        $twig = Factory::getEngine();
        $twig->addExtension(new TwigExtension(
            $c->get('router'),
            $c->get('request')->getUri()
        ));
        $twig->getEnvironment()->addGlobal('basket', $c->get(Basket::class));
        $twig->getEnvironment()->addGlobal('flash', $c->get(Flash::class));
        $twig->getEnvironment()->addGlobal('auth', $c->get(Auth::class));

        $random = mt_rand(100000, 999999);
        $twig->getEnvironment()->addGlobal('inc', $random);

        return $twig;
    },

    Guard::class => function (ContainerInterface $c) {
        $guard = new \Slim\Csrf\Guard;
        return $guard;
    },

    MailChimp::class => function (ContainerInterface $c) {
        $MailChimp = new MailChimp('acd7f1610fd49a1d4a535324add8d8bd-us1');
        return $MailChimp;
    },

    Mailer::class => function (ContainerInterface $c) {
        $config = array(
           'host' => getenv('MAIL_HOST'),
           'port' => getenv('MAIL_PORT'),
           'username' => getenv('MAIL_USERNAME'),
           'password' => getenv('MAIL_PASSWORD'),
            'from' => [
                'name' => getenv('MAIL_FROM_NAME'),
                'address' => getenv('MAIL_FROM_ADDRESS')
            ]
        );

        $transport = Swift_SmtpTransport::newInstance($config['host'], $config['port'])
            ->setUsername($config['username'])
            ->setPassword($config['password']);

        $swift = Swift_Mailer::newInstance($transport);

        return (new App\Mail\Mailer\Mailer($swift, $c->get(Twig::class)))
            ->alwaysFrom($config['from']['address'], $config['from']['name']);
    },

    Members::class => function (ContainerInterface $c) {
        return new Members;
    },

    MemberTypes::class => function (ContainerInterface $c) {
        return new MemberTypes;
    },

    SpecialistSkills::class => function (ContainerInterface $c) {
        return new SpecialistSkills;
    },

    Directory::class => function (ContainerInterface $c) {
        return new Directory;
    },

    Contacts::class => function (ContainerInterface $c) {
        return new Contacts;
    },

    MpaUser::class => function (ContainerInterface $c) {
        return new MpaUser;
    },

    UserStatus::class => function (ContainerInterface $c) {
        return new UserStatus;
    },

    Invoices::class => function (ContainerInterface $c) {
        return new Invoices;
    },

    Product::class => function (ContainerInterface $c) {
            return new Product;
    },

    Order::class => function (ContainerInterface $c) {
        return new Order;
    },

    Customer::class => function (ContainerInterface $c) {
        return new Customer;
    },

    Address::class => function (ContainerInterface $c) {
        return new Address;
    },

    Payment::class => function (ContainerInterface $c) {
        return new Payment;
    },

    Asset::class => function (ContainerInterface $c) {
        return new Asset;
    },

    Basket::class => function (ContainerInterface $c) {
        return new Basket(
            $c->get(SessionStorage::class),
            $c->get(Product::class)
        );
    },

    Auth::class => function (ContainerInterface $c) {
        return new Auth();
    },

    ProcessOrders::class => function (ContainerInterface $c) {
        return new ProcessOrders();
    },
];