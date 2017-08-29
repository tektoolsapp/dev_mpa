<?php

namespace App\Controllers\Reports;

use App\Models\User;
//use App\Models\MemberTypes;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

use App\Mail\Welcome;

class ReportsController extends Controller

{

    public function index($request, $response, $args)

    {

        //dump($this->container->mail);

        //die();

        /*
        $user = new User;

        $user->name = 'Allan';
        $user->email = 'allan.hyde@tektools.com.au';
        */

        /*
        $user = User::where([
            ['id', '>', 0]
        ])->get();
        */



        /*
        $user[] = (object) array(
            'email' => 'allan.hyde@tektools.com.au',
            'name' => 'Allan'
        );
        */

        $user = (object) array(
            'email' => 'sue@tektools.com.au',
            'name' => 'Sue'
        );


        //dump($user);

        //dump(compact('user'));

        //die();

        /*
        $this->container->mail->send('emails/welcome.twig', compact('user'), function($message)  use ($user) {

            $message->to($user->email)
                ->attach(__DIR__. '/../../../composer.json')
                ->subject("Welcome to MPA");

        });
        */

        //dump($this->container->mail->to($user->email, $user->name));

        //die();

        $this->container->mail->to($user->email, $user->name)->send(new Welcome($user));


        /*
        return $this->view->render($response, 'emails/welcome.twig', [

            'js_script' => 'test',

        ]);
        */


    }

}