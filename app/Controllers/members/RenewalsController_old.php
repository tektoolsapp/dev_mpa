<?php

namespace App\Controllers\Members;

use App\Models\Member;
use App\Models\Product;

use App\Mail\Renewal;

//use App\Models\MemberTypes;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class RenewalsController extends Controller

{

    public function index($request, $response)

    {

        $this->logger->addInfo("Renewals");

        $members = Member::getExpiredMembers();

        dump($members);

        return $this->view->render($response, 'members/all.renewals.twig', [

            'members' => $members,
            'js_script' => 'renewals'

        ]);

    }

    public function sendRenewalEmail($request, $response, $args)
    {

        //$id = $args['id'];

        $id = 1;

        $member = Member::where('id', $id)->get()->first();

        /*
        $user = (object) array(
            'email' => 'sue@tektools.com.au',
            'name' => 'Sue'
        );
        */

        $this->container->mail->to($member->business_email, $member->business_name)->send(new Renewal($member));

        $this->flash->addMessage('success', "Emailed");

        //$members = Member::getExpiredMembers();

        /*
        return $this->view->render($response, 'members/all.renewals.twig', [

            'members' => $members,
            'js_script' => 'renewals'

        ]);

        */

        return $response->withRedirect($this->router->pathFor('renewals.list'));

    }

    public function membershipPayment($request, $response)
    {

        /*
        $stripe = array(
            "secret_key"      => "sk_test_922RxybZoC3iETeV78zk6AsZ",
            "publishable_key" => "pk_test_iIWqogexsBna1ILCyxHO2ABi"
        );

        //\Stripe\Stripe::setApiKey($stripe['secret_key']);
        */

        //$products = Product::all();

        $products = Product::getAll();

        return $this->view->render($response, 'products/product.index.twig', [

            'products' => $products,
            'js_script' => 'renewals'

        ]);

    }

}