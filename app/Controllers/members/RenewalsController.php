<?php

namespace App\Controllers\Members;

use Slim\Router;
//use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;

use App\Mail\Renewal;
use App\Mail\Mailer\Mailer;

use App\Models\Member;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RenewalsController
{
    protected $router;
    protected $flash;
    protected $mail;

    public function __construct(Router $router, Flash $flash , Mailer $mail)
    {
        $this->router = $router;
        $this->flash = $flash;
        $this->mail = $mail;

        //dump($mail);

    }

    public function sendRenewalEmail(Request $request, Response $response)
    {

        //$id = $args['id'];

        $id = 1;

        $member = Member::where('id', $id)->get()->first();

        $this->mail->to($member->business_email, $member->business_name)->send(new Renewal($member));

        $this->flash->addMessage('success', "Emailed");

        return $response->withRedirect($this->router->pathFor('home'));

    }

}