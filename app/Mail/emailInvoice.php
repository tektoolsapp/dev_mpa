<?php

namespace App\Mail;

use App\Mail\Mailer\Mailable;

use App\Models\Member;

class EmailInvoice extends Mailable

{
    protected $member;

    public function __construct($member)
    {
        $this->member = $member;
    }

    public function build()
    {
        return $this->subject("MPA Invoice")
            ->view('emails/invoice.twig')
            ->attach('invoice_pdfs/invoice_flimsy_177_7.pdf')
            ->from('accounts@mpgawa.com.au', 'MPA Accounts')
            ->with([
                'member' => $this->member
            ]);
    }
}