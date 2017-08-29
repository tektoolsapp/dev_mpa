<?php

namespace App\Mail\Mailer;

use App\Mail\Mailer\Contracts\MailableContract;
use Swift_Mailer;
use Swift_Message;
use Slim\Views\Twig;
use App\Mail\Mailer\MessageBuilder;

class Mailer
{

    protected $swift;
    protected $twig;
    protected $from = [];

    public function __construct(Swift_Mailer $swift, Twig $twig)
    {
        $this->swift = $swift;
        $this->twig = $twig;
    }

    public function to($address, $name = null)
    {

        return (new PendingMailable($this))->to($address, $name);

    }

    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');

        //dump($this);

        return $this;

    }

    public function send($view, $viewData = [], Callable $callback = null)
    {

        if ($view instanceof MailableContract) {

            //dump('class');

            //return $view->send($this);

            return $this->sendMailable($view);


            //return;

        }

        $message = $this->buildMessage();

        //dump($message);

        call_user_func($callback, $message);

        $message->body($this->parseView($view, $viewData));

        //dump($message);

        return $this->swift->send($message->getSwiftMessage());

    }

    protected function sendMailable(Mailable $mailable)
    {

        //dump($this);

        //die();

        return $mailable->send($this);

    }

    protected function buildMessage()
    {

        //dump($this->from['address']);

        return (new MessageBuilder(new Swift_Message))

            ->from($this->from['address'], $this->from['name']);

    }

    protected function parseView($view, $viewData)
    {
        return $this->twig->fetch($view, $viewData);

    }

}