<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/', 'HomeController:index')->setName('home');

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
