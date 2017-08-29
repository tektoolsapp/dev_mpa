<?php
//use App\Middleware\AuthMiddleware;
//use App\Middleware\GuestMiddleware;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use App\Models\Invoices;

//HOME
$app->get('/', ['App\Controllers\HomeController', 'index'])->setName('home');

//AUTHENTICATION
$app->get('/auth/signin', ['App\Controllers\Auth\AuthController', 'getSignIn'])->setName('auth.signin');
$app->post('/auth/signin', ['App\Controllers\Auth\AuthController', 'postSignIn']);
$app->get('/auth/signiut', ['App\Controllers\Auth\AuthController', 'getSignOut'])->setName('auth.signout');

//MPA USERS
$app->get('/manageusers', ['App\Controllers\Settings\ManageUsersController', 'index'])->setName('manageusers.index');
$app->get('/manageusers/edit/{id}', ['App\Controllers\Settings\ManageUsersController', 'editUser'])->setName('manageusers.edit');
$app->get('/manageusers/new', ['App\Controllers\Settings\ManageUsersController', 'newUser'])->setName('manageusers.new');
$app->post('/manageusers/add', ['App\Controllers\Settings\ManageUsersController', 'createUser'])->setName('manageusers.add');
$app->post('/manageusers/update/{id}', ['App\Controllers\Settings\ManageUsersController', 'updateUser'])->setName('manageusers.update');

//SETTINGS
$app->get('/settings', ['App\Controllers\Settings\SettingsController', 'index'])->setName('settings.index');

//TRANSACTIONS
$app->map(['GET', 'POST'],'/invoices', ['App\Controllers\Transactions\InvoicesController', 'index'])->setName('invoices.index');
    //->add(new \App\Middleware\InvoiceDateRangeMiddleware($container->get(Twig::class), $container->get('router'), $container->get(Flash::class)))
    //->add(new \App\Middleware\TestMiddleware($container->get(Twig::class), $container->get('router'), $container->get(Flash::class), $container->get(Invoices::class)));
$app->get('/invoices/updates/{id}', ['App\Controllers\Transactions\InvoicesController', 'getUpdate'])->setName('invoices.updates');
$app->get('/invoices/prep/{id}', ['App\Controllers\Transactions\InvoicesController', 'prepExport'])->setName('invoices.prep.export');
$app->post('/invoices/myob_export', ['App\Controllers\Transactions\InvoicesController', 'export'])->setName('invoices.myob.export');
        //->add(new \App\Middleware\InvoiceExportErrorMiddleware($container->get(Twig::class), $container->get('router'), $container->get(Flash::class)));
$app->post('/invoices/myob_update', ['App\Controllers\Transactions\InvoicesController', 'exportUpdate'])->setName('invoices.myob.update');
$app->get('/invoices/email', ['App\Controllers\Transactions\InvoicesController', 'emailInvoice'])->setName('invoice.email');
$app->post('/invoices/errors', ['App\Controllers\Transactions\InvoicesController', 'invoiceError'])->setName('invoice.error');
$app->map(['GET', 'POST'],'/invoices/bydate', ['App\Controllers\Transactions\InvoicesController', 'getInvoicesbyDate'])->setName('invoices.bydate');
$app->get('/invoices/name/{name}', ['App\Controllers\Transactions\InvoicesController', 'getInvoicesByCustomerName'])->setName('invoice.bynam');

$app->get('/myob/connection', ['App\Controllers\Connections\MyobController', 'connect'])->setName('myob.connect');
$app->get('/myob/customers/get-cust/{get}', ['App\Controllers\Connections\MyobController', 'getCustomer'])->setName('myob.customer.get');
$app->post('/myob/customers/update-cust/{update}', ['App\Controllers\Connections\MyobController', 'updateCustomer'])->setName('myob.customer.update');

//PRODUCTS
$app->get('/products', ['App\Controllers\ProductController', 'index'])->setName('products');
$app->get('/products/{slug}', ['App\Controllers\ItemController', 'get'])->setName('product.get');
$app->get('/cart', ['App\Controllers\CartController', 'index'])->setName('cart.index');
$app->get('/cart/add/{slug}/{quantity}', ['App\Controllers\CartController', 'add'])->setName('cart.add');
$app->post('/cart/update/{slug}', ['App\Controllers\CartController', 'update'])->setName('cart.update');
$app->get('/order', ['App\Controllers\OrderController', 'index'])->setName('order.index');
$app->post('/order', ['App\Controllers\OrderController', 'create'])->setName('order.create');
$app->get('/order/{hash}', ['App\Controllers\OrderController', 'show'])->setName('order.show');

//PAYMENT GATEWAY
$app->get('/braintree/token', ['App\Controllers\BraintreeController', 'token'])->setName('braintree.token');

//MEMBERS
$app->get('/members/list/[{filter}]', ['App\Controllers\Members\MembersController', 'index'])->setName('members.index');
$app->get('/members/name/{name}', ['App\Controllers\Members\MembersController', 'getByName'])->setName('members.name');
$app->get('/members/update/{id}', ['App\Controllers\Members\MembersController', 'getUpdate'])->setName('members.update');
$app->get('/members/updates', ['App\Controllers\Members\MembersController', 'getMyobUpdates'])->setName('members.updates');
$app->get('/members/new', ['App\Controllers\Members\MembersController', 'newMember'])->setName('member.new');
$app->get('/members/edit/{id}', ['App\Controllers\Members\MembersController', 'get'])->setName('member.get');
$app->post('/members/edit/{id}', ['App\Controllers\Members\MembersController', 'edit'])->setName('member.edit');
$app->post('/members/add/[{id}]', ['App\Controllers\Members\MembersController', 'add'])->setName('member.add');
$app->get('/members/auto', ['App\Controllers\Members\MembersController', 'getAuto'])->setName('member.get.auto');
$app->get('/businesses', ['App\Controllers\Members\MembersController', 'getOld'])->setName('businesses.index');
$app->get('/export', ['App\Controllers\Members\MembersController', 'export'])->setName('businesses.export');

//EMAIL LISTS (MAILCHIMP)
$app->get('/email/lists', ['App\Controllers\Email\EmailController', 'index'])->setName('email.lists.index');
$app->get('/email/campaigns', ['App\Controllers\Email\EmailController', 'getCampaigns'])->setName('email.campaigns');
$app->get('/email/templates', ['App\Controllers\Email\EmailController', 'getTemplates'])->setName('email.templates');
$app->get('/email/list/{id}/[{name}]', ['App\Controllers\Email\EmailController', 'getList'])->setName('email.list');
$app->get('/email/recipient/new', ['App\Controllers\Email\EmailController', 'newRecipient'])->setName('email.recipient.new');
$app->get('/email/content/{id}', ['App\Controllers\Email\EmailController', 'getCampaignContent'])->setName('email.campaign.content');
$app->get('/email/new/list', ['App\Controllers\Email\EmailController', 'newList'])->setName('email.new.list');
$app->post('/email/updates/list/new', ['App\Controllers\Email\EmailController', 'addList'])->setName('email.list.add');
$app->post('/email/members/add/{id}', ['App\Controllers\Email\EmailController', 'addMembers'])->setName('email.list.members.add');


//STAKEHOLDERS
$app->get('/stakeholders/list/[{filter}]', ['App\Controllers\Stakeholders\StakeholdersController', 'index'])->setName('stakeholders.index');
$app->get('/stakeholder/update/{id}', ['App\Controllers\Stakeholders\StakeholdersController', 'getUpdate'])->setName('stakeholders.update');
$app->get('/stakeholders/updates', ['App\Controllers\Stakeholders\StakeholdersController', 'getMyobUpdates'])->setName('stakeholders.updates');
$app->get('/stakeholder/new', ['App\Controllers\Stakeholders\StakeholdersController', 'newStakeholder'])->setName('stakeholder.new');
$app->get('/stakeholder/edit/{id}', ['App\Controllers\Stakeholders\StakeholdersController', 'get'])->setName('stakeholder.get');
$app->post('/stakeholder/edit/{id}', ['App\Controllers\Stakeholders\StakeholdersController', 'edit'])->setName('stakeholder.edit');
$app->post('/stakeholder/add/[{id}]', ['App\Controllers\Stakeholders\StakeholdersController', 'add'])->setName('stakeholder.add');

//DIRECTORY LISTINGS
$app->get('/directory/edit/{id}', ['App\Controllers\Members\DirectoryController', 'get'])->setName('directory.get');
$app->get('/contact/get/{id}', ['App\Controllers\Members\ContactController', 'get'])->setName('contact.get');
$app->post('/contact/edit/{id}', ['App\Controllers\Members\ContactController', 'edit'])->setName('contact.edit');
$app->post('/contact/add', ['App\Controllers\Members\ContactController', 'add'])->setName('contact.add');
$app->get('/contacts/list/{filter}', ['App\Controllers\Members\ContactController', 'index'])->setName('contacts.index');
$app->get('/contacts/auto', ['App\Controllers\Members\ContactController', 'getAuto'])->setName('contacts.get.auto');
$app->get('/contacts/name/{name}', ['App\Controllers\Members\ContactController', 'getByName'])->setName('contacts.name');
$app->get('/contacts/member/{name}', ['App\Controllers\Members\ContactController', 'getMemberByName'])->setName('contacts.member.name');

//FLIMSYS
$app->get('/flimsys', ['App\Controllers\Flimsys\FlimsysController', 'index'])->setName('flimsys.index');
$app->get('/flimsys/new',['App\Controllers\Flimsys\FlimsysController', 'newRequest'])->setName('flimsys.new');
$app->post('/flimsys/add', ['App\Controllers\Flimsys\FlimsysController', 'add'])->setName('flimsys.add');
$app->post('/flimsys/edit/{id}', ['App\Controllers\Flimsys\FlimsysController', 'edit'])->setName('flimsys.edit');
$app->get('/flimsys/get/{id}/{source}/[{status}]', ['App\Controllers\Flimsys\FlimsysController', 'get'])->setName('flimsys.get');
$app->get('/flimsys/member/{name}', ['App\Controllers\Flimsys\FlimsysController', 'getMemberByName'])->setName('flimsys.member.name');
$app->get('/flimsys/contact/{id}', ['App\Controllers\Flimsys\FlimsysController', 'getContactById'])->setName('flimsys.contact.id');
$app->post('/flimsys/order/{id}', ['App\Controllers\Flimsys\FlimsysController', 'orderFlimsy'])->setName('flimsys.order');

//EVENTS

$app->get('/events', ['App\Controllers\Events\EventsController', 'index'])->setName('events.index');
$app->get('/event/new',['App\Controllers\Events\EventsController', 'newEvent'])->setName('event.new');
$app->post('/event/add', ['App\Controllers\Events\EventsController', 'add'])->setName('event.add');
$app->get('/event/get/{id}', ['App\Controllers\Events\EventsController', 'get'])->setName('event.get');
$app->post('/event/edit/{id}', ['App\Controllers\Events\EventsController', 'edit'])->setName('event.edit');
$app->get('/event/attendees/{id}', ['App\Controllers\Events\EventsController', 'attendees'])->setName('event.attendees');
$app->get('/event/planning/{id}', ['App\Controllers\Events\EventsController', 'planning'])->setName('event.planning');



//MISC
//$app->get('/products/test', ['App\Controllers\products\TestProductController', 'test'])->setName('product.test');

//$app->get('/events/pdf', ['App\Controllers\Events\EventsController', 'pdf'])->setName('events.pdf')$app->get('/members/list/[{filter}]', ['App\Controllers\Members\MembersController', 'index'])->setName('members.index');
/*
$app->get('/members/name/{name}', ['App\Controllers\Members\MembersController', 'getByName'])->setName('members.name');
$app->get('/members/update/{id}', ['App\Controllers\Members\MembersController', 'getUpdate'])->setName('members.update');
$app->get('/members/updates', ['App\Controllers\Members\MembersController', 'getMyobUpdates'])->setName('members.updates');
$app->get('/members/new', ['App\Controllers\Members\MembersController', 'newMember'])->setName('member.new');
$app->get('/members/edit/{id}', ['App\Controllers\Members\MembersController', 'get'])->setName('member.get');
$app->post('/members/edit/{id}', ['App\Controllers\Members\MembersController', 'edit'])->setName('member.edit');
$app->post('/members/add/[{id}]', ['App\Controllers\Members\MembersController', 'add'])->setName('member.add');
$app->get('/members/auto', ['App\Controllers\Members\MembersController', 'getAuto'])->setName('member.get.auto');
*/




$app->get('/events/invoice', ['App\Controllers\Events\EventsController', 'invoice'])->setName('events.invoice');

$app->get('/email', ['App\Controllers\Members\RenewalsController', 'sendRenewalEmail'])->setName('renewals.email');

$app->post('/flimsys/upload', ['App\Controllers\Flimsys\FlimsysController', 'upload'])->setName('flimsys.upload')
    ->add(new \App\Middleware\FileFilterMiddleWare($container->get(Twig::class), $container->get('router'), $container->get(Flash::class)));
;

//$app->get('/email/[{id}]', 'RenewalsController:sendRenewalEmail')->setName('renewals.email');


//$app->get('/flimsys', 'FlimsysController:index')->setName('flimsys.main');



//$app->get('/contacts/list/{status}/[{name}]', 'ContactsController:index')->setName('contacts.list');



//$app->get('/directory/edit/{id}', 'DirectoryController:get')->setName('directory.get');



//$app->get('/members/edit/{id}', 'MembersController:displayMember')->setName('members.edit');


//$app->get('/members/list/{status}/[{name}]', 'MembersController:index')->setName('members.list');




//$app->get('/cart/add/{slug}/{quantity}', ['App\Controllers\CartController', 'add'])->setName('cart.add');


//$app->get('/', ['App\Controllers\HomeController', 'index'])->setName('home');

/*
$app->group('', function(){

    $this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');

    $this->post('/auth/signin', 'AuthController:postSignIn');

})->add(new GuestMiddleWare($container));

$app->group('', function(){

    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');

    //$this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');

    //$this->post('/auth/password/change', 'PasswordController:postChangePassword');

})->add(new AuthMiddleWare($container));


$app->get('/members/list/{status}/[{name}]', 'MembersController:index')->setName('members.list');

$app->get('/members/add/[{id}]', 'MembersController:newMember')->setName('members.add');

$app->post('/members/add/[{id}]', 'MembersController:addMember');

$app->get('/members/edit/{id}', 'MembersController:displayMember')->setName('members.edit');

$app->post('/members/upd/[{id}]', 'MembersController:editMember')->setName('members.upd');

$app->get('/members/auto', 'MembersController:getMembersAuto')->setName('member.auto.get');


$app->get('/members/edit/directory/{id}', 'DirectoryController:index')->setName('directory.edit');

$app->get('/members/renewals/[{id}]', 'RenewalsController:index')->setName('renewals.list');

$app->get('/members/renewals/email/[{id}]', 'RenewalsController:sendRenewalEmail')->setName('renewals.email');

$app->get('/members/renewals/payment/[{id}]', 'RenewalsController:membershipPayment')->setName('membership.payment');


$app->get('/members/renewals/products/[{slug}]', 'ProductController:get')->setName('product.get');

//$app->get('/members/renewals/products/[{slug}]', ['App\Controllers\Products\ProductController', 'get'])->setName('product.get');

$app->get('/cart', 'CartController:index')->setName('cart.index');

$app->get('/cart/add/{slug}/{quantity}', 'CartController:add')->setName('cart.add');



//$app->get('/members/directory/add/[{id}]', 'DirectoryController:adddirectory')->setName('directory.add');

$app->map(['GET', 'POST'],'/members/directory/edit/[{id}]', 'DirectoryController:editDirectory')->setName('directory.update');






$app->post('/members/contacts/add/[{id}]', 'MembersController:addContact')->setName('member.contact.add');
$app->post('/members/contacts/edit/{id}', 'MembersController:editContact')->setName('member.contact.edit');

$app->get('/members/contacts/get/{id}', 'MembersController:getContact')->setName('member.contact.get');

$app->get('/contacts/auto', 'ContactsController:getContactsAuto')->setName('contact.auto.get');

$app->get('/contacts/list/{status}/[{name}]', 'ContactsController:index')->setName('contacts.list');




$app->get('/events', 'EventsController:index')->setName('events.main');

$app->get('/flimsys', 'FlimsysController:index')->setName('flimsys.main');

$app->get('/reports', 'ReportsController:index')->setName('reports.main');

$app->get('/contacts', 'ContactsController:index')->setName('contacts.main');


$app->group('', function(){

    $this->get('/settings', 'SettingsController:index')->setName('settings.main');

    $this->get('/settings/manageusers', 'ManageUsersController:index')->setName('manage.users.main');

    $this->get('/settings/manageusers/add/[{id}]', 'ManageUsersController:newUser')->setName('new.user');

    $this->post('/settings/manageusers/add/[{id}]', 'ManageUsersController:postNewUser');

    $this->get('/settings/manageusers/edit/[{id}]', 'ManageUsersController:displayUser')->setName('edit.user');

    $this->post('/settings/manageusers/edit/[{id}]', 'ManageUsersController:editUser')->setName('update.user');

})->add(new AuthMiddleWare($container));

*/
