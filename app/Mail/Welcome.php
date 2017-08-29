<?php

namespace App\Mail;

use App\Mail\Mailer\Mailable;

use App\Models\User;

class Welcome extends Mailable

{

    protected $user;

    //public function __construct(User $user)

    public function __construct($user)

    {

        $this->user = $user;

    }

    public function build()
    {

            return $this->subject("Welcome to the MPA")
                ->view('emails/welcome.twig')
                ->attach(__DIR__. '/../../composer.json')
                ->attach(__DIR__. '/../../composer.lock')
                ->from('membership@mapgwa.com.au', 'MPA Membership')
                ->with([
                    'user' => $this->user
                ]);

    }

}