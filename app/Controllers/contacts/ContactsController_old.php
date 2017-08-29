<?php

namespace App\Controllers\Contacts;

use App\Models\Contacts;
use App\Controllers\Controller;

class ContactsController extends Controller

{

    public function index($request, $response, $args)

    {

        if($args['status'] != 'all') {

            $contact_status = explode(",", $args['status']);

        } else {

            $contact_status = $args['status'];

        }

        $contact_name = $args['name'];

        if(isset($contact_status) && $contact_status != 'all' && !empty($contact_status[0])) {

            $contacts = Contacts::whereIn('status', $contact_status)->paginate(20);

        } elseif(isset($contact_name) && !empty($contact_name)) {

            $contacts = Contacts::where('fullname', $contact_name)->get();
            $display_name = $contact_name;

        } else {

            $contacts = Contacts::where('id', '>', 0)->paginate(20);

            $display_name = '';

        }

        $_SESSION['contacts_display_status'] = $args['status'];
        $_SESSION['contacts_display_name'] = $display_name;

        $this->flash->getMessages();

        return $this->view->render($response, 'contacts/all.contacts.twig', [

            'contacts' => $contacts,
            'js_script' => 'contacts',
            'contacts_display_status' => $args['status'],
            'contacts_display_name' => $display_name

        ]);

    }

    public function getContactsAuto($request, $response)
    {

        $term = $request->getParam('term');

        $contacts = Contacts::where('fullname', 'like', '%' . $term . '%')->get();

        $contact_names = array();

        foreach ($contacts as $contact) {

            $contact_names[]  = $contact->fullname;
        }

        return json_encode($contact_names);

    }

}