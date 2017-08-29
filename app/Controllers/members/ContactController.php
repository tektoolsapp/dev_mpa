<?php

namespace App\Controllers\Members;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Members;
use App\Models\Contacts;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;

class ContactController
{
    protected $router;
    protected $validator;
    protected $flash;
    protected $contacts;
    protected $members;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash, Contacts $contacts, Members $members)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->contacts = $contacts;
        $this->members = $members;
    }

    public function index ($filter, Request $request, Response $response, Twig $view)
    {
        $filters = $request->getQueryParams();

        //dump("FILTERS 1", $filters);
        //dump("SIZE", sizeof($filters));

        if($filter == 'all' && sizeof($filters) == 0) {
            unset($_SESSION['contacts_filter_query']);
            //RESET THE PAGINATOR TO PAGE 1
            $_SESSION['current_page'] = 1;
            $filter_query = '';
        } elseif($filter == 'all' && sizeof($filters) == 1 && isset($filters['page'])) {
            //RESET THE PAGINATOR TO PAGE 1
            $_SESSION['current_page'] = $filters['page'];
            $filter_query = http_build_query($filters);
            $_SESSION['contacts_filter_query'] = $filters;
        } else {
            $filters = $request->getQueryParams();
            //dump("FILTERS 2", $filters);

            foreach($filters as $key => $value) {
                ${$key} = $value;
            }
            $filter_query = $_GET;
        }

        $contacts = $this->contacts->where('status' ,'<>', 'I')
            ->when($journal_opt, function ($q) use ($journal_opt) {
                return $q->where('journal', $journal_opt);
            })
            ->when($member_id, function ($q) use ($member_id) {
                return $q->where('members_id', $member_id);
            })
            ->paginate(40)->appends($_GET);

        foreach($contacts as $contact){
            $contact_type = $contact->type;
            $contact_type_desc = $this->contacts->contactType($contact_type);
            $contact['type_desc'] = $contact_type_desc[0]->description;
            $contact_entity = $contact->members_id;
            $entity_desc = $this->members->getMemberTradingName($contact_entity);
            $contact['entity_name'] = $entity_desc->business_name;
        }

        //SET PAGE DISPLAY TOTALS
        $contact_total = $contacts->total();
        $contact_count = count($contacts);

        return $view->render($response, 'contacts/contacts.index.twig', [
            'contacts' => $contacts,
            'js_script' => 'contacts',
            'filter_query' => $filter_query,
            'update_source' => 'C',
            'contact_total' => $contact_total,
            'contact_count' => $contact_count,
        ]);
    }

    public function get($id, Request $request, Response $response, Twig $view)
    {
        $contact = $this->contacts->where('id', $id)->get()->first();
        return json_encode($contact);
    }

    public function getAuto(Request $request, Response $response, Twig $view)
    {
        $term = $request->getParam('term');
        $contacts = $this->contacts->where('fullname', 'like', '%' . $term . '%')->get();
        $fullnames = array();

        foreach ($contacts as $contact) {
            $fullnames[]  = $contact->fullname;
        }

        return json_encode($fullnames);
    }

    public function getByName($name, Request $request, Response $response, Twig $view)
    {
        $contact = $this->contacts->where('fullname' ,'=', $name)->get()->first();
        $contact_type = $contact->type;
        $contact_type_desc = $this->contacts->contactType($contact_type);
        $contact['type_desc'] = $contact_type_desc[0]->description;
        $contact_entity = $contact->members_id;
        $entity_desc = $this->members->getMemberTradingName($contact_entity);
        $contact['entity_name'] = $entity_desc->business_name;
        $contact_array[] = $contact;

        return $view->render($response, 'contacts/contacts.index.twig', [
            'contacts' => $contact_array,
            'js_script' => 'contacts'
        ]);
    }

    public function getMemberByName($name, Request $request, Response $response, Twig $view)
    {
        $member = $this->members->getMemberByName($name);
        //dump($member->id);
        echo $member->id;
    }

    public function add(Request $request, Response $response, Twig $view)
    {
        $full_name = $request->getParam('contact_firstname'). " " .$request->getParam('contact_surname');

        $guid = $this->GUID();
        $row_version = $this->uniqidReal();

        $new_contact = $this->contacts->firstorcreate([
            'guid' => $guid,
            'type' => $request->getParam('contact_type'),
            'role' => $request->getParam('contact_role'),
            'members_id' => $request->getParam('contact_members_id'),
            'firstname' => $request->getParam('contact_firstname'),
            'surname' => $request->getParam('contact_surname'),
            'fullname' => $full_name,
            'phone' => $request->getParam('contact_phone'),
            'mobile' => $request->getParam('contact_mobile'),
            'fax' => $request->getParam('contact_fax'),
            'email' => $request->getParam('contact_email'),
            'journal' => $request->getParam('contact_journal'),
            'status' => $request->getParam('contact_status'),
            'row_version' => $row_version
        ]);

        //UPDATE THE PRIMARY CONTACT FOR THE MEMBER
        if($request->getParam('contact_role') == 'P') {
            //UPDATE ANY REPLACED PRIMARY CONTACT TO OTHER CONTACT TYPE
            $this->contacts->where('role', "P")
                ->where('members_id', $request->getParam('contact_members_id'))
                ->where('id', '<>', $new_contact)
                ->update([
                    'role' => "O",
            ]);

            $this->members->where('id', $request->getParam('contact_members_id'))
                ->update([
                'primary_contact' => $request->getParam('contact_id'),
            ]);
        } else {
            $num_primary = $this->contacts->where(['role' => 'P', 'members_id' => $request->getParam('contact_members_id')])->count();
        }

        $add_contact = "New Contact " . $request->getParam('contact_firstname'). " ".$request->getParam('contact_surname'). " have been Added!";

        $this->flash->addMessage('contact', $add_contact);
    }

    public function edit($id, Request $request, Response $response, Twig $view)
    {
        $full_name = $request->getParam('contact_firstname'). " " .$request->getParam('contact_surname');

        //dump("JOURNAL:", $request->getParam('contact_journal'));
        //die();

        $this->contacts->where('id', $request->getParam('contact_id'))
            ->update([
                'role' => $request->getParam('contact_role'),
                'firstname' => $request->getParam('contact_firstname'),
                'surname' => $request->getParam('contact_surname'),
                'fullname' => $full_name,
                'phone' => $request->getParam('contact_phone'),
                'mobile' => $request->getParam('contact_mobile'),
                'fax' => $request->getParam('contact_fax'),
                'email' => $request->getParam('contact_email'),
                'journal' => $request->getParam('contact_journal'),
                'status' => $request->getParam('contact_status'),
            ]);

        //UPDATE THE PRIMARY CONTACT FOR THE MEMBER
        if($request->getParam('contact_role') == 'P') {
            //UPDATE ANY REPLACED PRIMARY CONTACT TO OTHER CONTACT TYPE
            $this->contacts->where('role', "P")
                ->where('members_id', $request->getParam('contact_members_id'))
                ->where('id', '<>', $request->getParam('contact_id'))
                ->update([
                    'role' => "O",
                ]);

            $this->members->where('id', $request->getParam('contact_members_id'))
                ->update([
                    'primary_contact' => $request->getParam('contact_id'),
                ]);
        } else {
            $num_primary = $this->contacts->where(['role' => 'P', 'members_id' => $request->getParam('contact_members_id')])->count();
        }

        $update_contact = "Contact Details for " . $request->getParam('contact_firstname'). " ".$request->getParam('contact_surname'). " have been Updated!";

        $this->flash->addMessage('contact', $update_contact);
    }

    public function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function uniqidReal($lenght = 13) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

}