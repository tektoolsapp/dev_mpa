<?php

namespace App\Mail;

use App\Mail\Mailer\Mailable;

use App\Models\Member;

class Renewal extends Mailable

{

    protected $member;

    //public function __construct(User $user)

    public function __construct($member)

    {

        $this->member = $member;

    }

    public function build()
    {

            return $this->subject("MPA Membership Renewal X")
                ->view('emails/membership.renewal.twig')
                //->attach(__DIR__. '/../../composer.json')
                ->from('membership@mpgawa.com.au', 'MPA Membership')
                ->with([
                    'member' => $this->member
                ]);

    }

}