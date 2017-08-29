<?php

namespace App\Controllers\Stakeholders;

use Slim\Router;
use Slim\Views\Twig;
//use App\Models\Members;
use App\Models\Stakeholders;
//use App\Models\ActivityTypes;
//use App\Models\MemberTypes;
use App\Models\Contacts;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Forms\StakeholderForm;
use Illuminate\Database\Capsule\Manager as DB;

class StakeholdersController
{
    protected $router;
    protected $validator;
    protected $flash;
    protected $stakeholders;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash, Stakeholders $stakeholders)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->stakeholders = $stakeholders;
    }

    public function index($filter, Request $request, Response $response, Twig $view)
    {
        //UNSET MYOB INTEGRATION SESSION VARS
        unset($_SESSION['myob_customer_updates']);
        unset($_SESSION['get_trading_name']);

        //dump("FILTER", $filter);
        //dump('SC', $_SESSION['members_filter_query']);

        $filters = $allGetVars = $request->getQueryParams();

        //dump("FILTERS 1", $filters);
        //dump("SIZE", sizeof($filters));

        if($filter == 'all' && sizeof($filters) == 0) {
            unset($_SESSION['members_filter_query']);

            //RESET THE PAGINATOR TO PAGE 1
            $_SESSION['current_page'] = 1;
        } elseif($filter == 'all' && sizeof($filters) == 1 && isset($filters['page'])) {
            //RESET THE PAGINATOR TO PAGE 1
            $_SESSION['current_page'] = $filters['page'];
            $filter_query = http_build_query($filters);
            $_SESSION['members_filter_query'] = $filters;
        } elseif($filter != 'all' && sizeof($filters) == 1) {
            //$filter_query = http_build_query($filters);
            $_SESSION['members_filter_query'] = $filters;

            if(isset($filters['types'])){
                $types = $filters['types'];
            } else {
                $types = null;
                }

            $filter_query = $_GET;
            $_SESSION['current_page'] = 1;
        } else {
            $filters = $allGetVars = $request->getQueryParams();
            $_SESSION['members_filter_query'] = $filters;

            if(isset($filters['types'])){
                $types = $filters['types'];
            } else {
                $types = null;
            }

            $_SESSION['current_page'] = 0;
            $filter_query = $_GET;
        }

        $stakeholders = $this->stakeholders->getStakeholders($types);

        //SET PAGE DISPLAY TOTALS
        $stakeholders_total = $stakeholders->total();
        $stakeholders_count = count($stakeholders);

        return $view->render($response, 'stakeholders/stakeholders.index.twig', [
            'members' => $stakeholders,
            'member_total' => $stakeholders_total,
            'member_count' => $stakeholders_count,
            'js_script' => 'stakeholders_list',
            'filter_query' => $filter_query,
        ]);
    }

    public function newStakeholder(Request $request, Response $response, Twig $view, Stakeholders $stakeholders, Contacts $contacts)
    {
        $name = $_SESSION['get_trading_name'];

        $stakeholder_types = DB::table('stakeholder_types')->where('member_type_status', 'A')->get();
        $stakeholder_activity_types = DB::table('stakeholder_activity_types')->where('activity_type_status', 'A')->get();

        return $view->render($response, 'stakeholders/stakeholder.update.twig', [
            'mode' => 'add',
            'js_script' => 'stakeholders',
            'js_script_2' => 'contacts',
            'integ_connect_script' => 'myob_connect',
            'integ_process_script_2' => 'myob_customers',
            'member_types' => $stakeholder_types,
            'activity_types' => $stakeholder_activity_types,
            'get_trading_name' => $name
        ]);
    }

    public function getAuto(Request $request, Response $response, Twig $view, Contacts $contacts)
    {
        $term = $request->getParam('term');
        $members = Members::where('business_name', 'like', '%' . $term . '%')->get();
        $company_names = array();

        foreach ($members as $member) {
            $company_names[]  = $member->business_name;
        }

        return json_encode($company_names);
    }

    public function getByName($name, Request $request, Response $response, Twig $view)
    {
        $_SESSION['get_trading_name'] = $name;

        $member[] = $this->members->getMemberByName($name);
        $member_total = 1;
        $member_count = 1;

        return $view->render($response, 'members/members.index.twig', [
            'members' => $member,
            'js_script' => 'members_list',
            'member_total' => $member_total,
            'member_count' => $member_count
        ]);
    }

    public function getMyobUpdates(Request $request, Response $response, Twig $view)
    {
        $updates = $this->stakeholders->where('myob_integ_status', 'N')->select('stakeholders.id')->get();
        $update_array = array();

        foreach ($updates as $update) {
            $update_array[]  = $update->id;
        }

        return json_encode($update_array);
    }

    public function getUpdate($id, Request $request, Response $response, Twig $view)
    {
        $_SESSION['update_myob_member'] = $id;
        $stakeholder = $this->stakeholders->where('id', $id)->get()->first();

        //PROCESS AND RETURN THE ARRAY
        $json_update_arr = array();
        $customer_array = array();
        $n = rand(0,100000);
        $customer_card_id = $n;
        //SET IN DOTENV
        $country = 'Australia';
        $myob_active_status = true;
        $customer_card_api = $customer_card_id;
        $company_api_uid = getenv('API_COMPANY_FILE');
        $customer_api_uid = $stakeholder->myob_uid;
        $_SESSION['customer_uid'] = $customer_api_uid;

        $customer_row = $stakeholder->myob_row;

        if($stakeholder->business_type == 'B') {
            $is_individual = 'false';
            $last_name = "";
            $first_name = "";
            $company_name = $stakeholder->company_name;
        } else {
            $is_individual = 'true';
            //GET NAMES FROM PRIMARY CONTACT
            $first_name = 'Another';
            $last_name = 'Member';
        }

        $_SESSION['myob_update_customer_name'] = $stakeholder->business_name;
        //GET FROM PRIMARY CONTACT
        $contact_phone = '0408702047';
        $customer_contact = $first_name." ".$last_name;

        if(!empty($customer_tax_uid)) {
            $tax_code_uid = getenv('API_GST_TAXCODE');
        } else {
            $tax_code_uid = getenv('API_GST_TAXCODE');
        }
        $freight_tax_code_uid = $tax_code_uid;
        $tax_code_array = array();
        $tax_code_array['UID'] = $tax_code_uid;
        $freight_tax_code_array = array();
        $freight_tax_code_array['UID'] = $freight_tax_code_uid;

        //PAYMENT TERMS
        $payment_terms_array = array();

        $payment_is_due = 'InAGivenNumberOfDays';
        $num_days = 7;

        $payment_terms_array['PaymentIsDue'] = $payment_is_due;
        $payment_terms_array['BalanceDueDate'] = $num_days;

        //ADD CUSTOMER ACCOUNT TO PRODUCT/MEMBER
        $customer_acct_uid = '4-1000';
        $gl_sales_uid = getenv('API_INCOME_ACCOUNT');

        if(empty($customer_api_uid)) {
            //Addresses
            $address_details_array = array();
            $address_array = array();
            $address_array['Location'] = 1;
            $address_array['Street'] = $stakeholder->business_address;
            $address_array['City'] = $stakeholder->business_suburb;
            $address_array['State'] = $stakeholder->business_state;
            $address_array['PostCode'] = $stakeholder->business_postcode;
            $address_array['Country'] = $country;
            $address_array['Phone1'] = $stakeholder->business_phone;
            $address_array['Phone2'] = $contact_phone;
            $address_array['Phone3'] = null;
            $address_array['Fax'] = $stakeholder->business_fax;
            $address_array['Email'] = $stakeholder->business_email;
            $address_array['Website'] = null;
            $address_array['ContactName'] = $customer_contact;
            $address_array['Salutation'] = null;
            $address_details_array[] = $address_array;
            //Account
            $gl_account_array = array();
            if(!empty($customer_acct_uid)) {
                $gl_account_array['UID'] = $gl_sales_uid;
            } else {
                $gl_account_array['UID'] = $gl_sales_uid;
            }

            //SellingDetails
            $selling_details_array = array();
            $selling_details_array['IncomeAccount'] = $gl_account_array;
            $selling_details_array['ABN'] = $stakeholder->business_abn;
            $selling_details_array['TaxCode'] = $tax_code_array;
            $selling_details_array['FreightTaxCode'] = $freight_tax_code_array;
            $selling_details_array['Terms'] = $payment_terms_array;

            //customer array
            $customer_array['DisplayID'] = $customer_card_api;
            $customer_array['IsActive'] = $myob_active_status;
            $customer_array['UID'] = base64_encode($customer_api_uid);
            $customer_array['CompanyName'] = $stakeholder->business_name;
            $customer_array['LastName'] = $last_name;
            $customer_array['FirstName'] = $first_name;
            $customer_array['IsIndividual'] = $is_individual;
            $customer_array['Addresses'] = $address_details_array;
            $customer_array['SellingDetails'] = $selling_details_array;

        } else {
            //Addresses
            $address_details_array = array();
            $address_array = array();
            $address_array['Location'] = 1;
            $address_array['Street'] = $stakeholder->business_address;
            $address_array['City'] = $stakeholder->business_suburb;
            $address_array['State'] = $stakeholder->business_state;
            $address_array['PostCode'] = $stakeholder->business_postcode;
            $address_array['Country'] = $country;
            $address_array['Phone1'] = $stakeholder->business_phone;
            $address_array['Phone2'] = $contact_phone;
            $address_array['Phone3'] = null;
            $address_array['Fax'] = $stakeholder->business_fax;
            $address_array['Email'] = $stakeholder->business_email;
            $address_array['Website'] = null;
            $address_array['ContactName'] = $customer_contact;
            $address_array['Salutation'] = null;
            $address_details_array[] = $address_array;

            //Account
            $gl_account_array = array();
            if(!empty($customer_acct_uid)) {
                $gl_account_array['UID'] = $gl_sales_uid;
            } else {
                $gl_account_array['UID'] = $gl_sales_uid;
            }

            //SellingDetails
            $selling_details_array = array();
            $selling_details_array['IncomeAccount'] = $gl_account_array;
            $selling_details_array['ABN'] = $stakeholder->business_abn;
            $selling_details_array['TaxCode'] = $tax_code_array;
            $selling_details_array['FreightTaxCode'] = $freight_tax_code_array;
            $selling_details_array['Terms'] = $payment_terms_array;

            //customer array
            $customer_array['DisplayID'] = $customer_card_api;
            $customer_array['IsActive'] = $myob_active_status;
            $customer_array['UID'] = base64_encode($customer_api_uid);
            $customer_array['CompanyName'] = $stakeholder->business_name;
            $customer_array['LastName'] = $last_name;
            $customer_array['FirstName'] = $first_name;
            $customer_array['IsIndividual'] = $is_individual;
            $customer_array['Addresses'] = $address_details_array;
            $customer_array['SellingDetails'] = $selling_details_array;
        }

        $json_update = json_encode($customer_array,JSON_HEX_APOS);

        return $json_update;
    }

    public function get($id, Request $request, Response $response, Twig $view, Contacts $contacts)
    {
        $stakeholder = $this->stakeholders->where('id', $id)->get()->first();
        $name = $_SESSION['get_trading_name'];

        $_SESSION['check_member_mailing'] = $stakeholder['set_mailing_address'];

        $contacts = $this->stakeholders->find($stakeholder->id)->contacts()
        ->leftJoin('contact_types', 'contacts.type', '=', 'contact_types.type')
        ->leftJoin('contact_roles', 'contacts.role', '=', 'contact_roles.type')
        ->leftJoin('contact_status', 'contacts.status', '=', 'contact_status.type')
        ->select(
            'contacts.id',
            'contacts.fullname',
            'contacts.phone',
            'contacts.mobile',
            'contacts.email',
            'contact_types.description as type_desc',
            'contact_roles.description as role_desc',
            'contact_status.description as status_desc'
        )->orderBy('contacts.id', 'ASC')
        ->get();

        if(isset($_SESSION['members_filter_query'])){
            $filter_query = http_build_query($_SESSION['members_filter_query']);
        } else {
            $filter_query = '';
        }

        $stakeholder_types = DB::table('stakeholder_types')->where('member_type_status', 'A')->get();
        $stakeholder_activity_types = DB::table('stakeholder_activity_types')->where('activity_type_status', 'A')->get();

        return $view->render($response, 'stakeholders/stakeholder.update.twig', [
            'mode' => 'edit',
            'js_script' => 'stakeholders',
            'js_script_2' => 'contacts',
            'integ_connect_script' => 'myob_connect',
            'integ_process_script_2' => 'myob_customers',
            'member' => $stakeholder,
            'member_types' => $stakeholder_types,
            'activity_types' => $stakeholder_activity_types,
            'contacts' => $contacts,
            'filter_query' => $filter_query,
            'update_source' => 'M',
            'get_trading_name' => $name
        ]);
    }

    public function edit($id, Request $request, Response $response, Twig $view, Stakeholders $stakeholders, Contacts $contacts)
    {
        if($request->getParam('set_mailing_address') == 'on'){
            $_SESSION['check_member_mailing'] = 'N';
        } else {
            $_SESSION['check_member_mailing'] = 'Y';
        }

        if(isset($_SESSION['old'])) {
            $_SESSION['old']['check_set_mailing_address'] = $_SESSION['check_member_mailing'];
        }

        //$validation = $this->validator->validate($request, StakeholderForm::rules());

        /*
        if ($validation->fails()) {
            return 'errors';
        } else {
        */

            if($_SESSION['check_member_mailing'] == 'N'){
                $mailing_address = $request->getParam('business_address');
                $mailing_suburb = $request->getParam('business_suburb');
                $mailing_state = $request->getParam('business_state');
                $mailing_postcode = $request->getParam('business_postcode');
            } else {
                $mailing_address = $request->getParam('mailing_address');
                $mailing_suburb = $request->getParam('mailing_suburb');
                $mailing_state = $request->getParam('mailing_state');
                $mailing_postcode = $request->getParam('mailing_postcode');
            }

            $this->stakeholders->where('id', $request->getParam('member_id'))
                ->update([
                    'myob_integ_status' => 'N',
                    'business_name' => $request->getParam('business_name'),
                    'company_name' => $request->getParam('company_name'),
                    'business_abn' => $request->getParam('business_abn'),
                    'business_acn' => $request->getParam('business_acn'),
                    'business_arbn' => $request->getParam('business_arbn'),
                    'business_type' => $request->getParam('business_type'),
                    'member_type' => $request->getParam('member_type'),
                    'member_status' => $request->getParam('member_status'),
                    'activity_type' => $request->getParam('activity_type'),
                    'business_phone' => $request->getParam('business_phone'),
                    'business_fax' => $request->getParam('business_fax'),
                    'business_email' => $request->getParam('business_email'),
                    'accounts_email' => $request->getParam('accounts_email'),
                    'business_address' => $request->getParam('business_address'),
                    'business_suburb' => $request->getParam('business_suburb'),
                    'business_state' => $request->getParam('business_state'),
                    'business_postcode' => $request->getParam('business_postcode'),
                    'set_mailing_address' => $_SESSION['check_member_mailing'],
                    'mailing_address' => $mailing_address,
                    'mailing_suburb' => $mailing_suburb,
                    'mailing_state' => $mailing_state,
                    'mailing_postcode' => $mailing_postcode
                ]);

            $this->flash->addMessage('info', 'Stakeholder details were successfully updated!');

            $return_array = array(
                'next' => 'ok',
                'update_id' => $request->getParam('member_id')
            );

            return json_encode($return_array);
        //}
    }

    public function add(Request $request, Response $response, Twig $view)
    {
        if($request->getParam('set_mailing_address') == 'on'){
            $_SESSION['check_member_mailing'] = 'N';
        } else {
            $_SESSION['check_member_mailing'] = 'Y';
        }

        if(isset($_SESSION['old'])) {
            $_SESSION['old']['check_set_mailing_address'] = $_SESSION['check_member_mailing'];
        }

        //SESSION VARS FOR VALIDATION
        $_SESSION['stored_company_name'] = 'addingamember';
        $_SESSION['member_acn_number'] = $request->getParam('business_acn');
        $_SESSION['member_arbn_number'] = $request->getParam('business_arbn');

        //$validation = $this->validator->validate($request, StakeholderForm::rules());

        //dump($validation);

        /*
        if ($validation->fails()) {
            return 'errors';
        } else {
        */
            $guid = $this->GUID();
            $row_version = $this->uniqidReal();
            $posting_array = $request->getParams();
            $licence_type = array();

            /*
            foreach ($posting_array as $key => $value) {
                if (substr($key, 0, 10) == 'checkboxes') {
                    $licence_type[$key] = $value;
                }
            }
            */

            //$post_license_types = json_encode($licence_type);

            if($_SESSION['check_member_mailing'] == 'N'){
                $mailing_address = $request->getParam('business_address');
                $mailing_suburb = $request->getParam('business_suburb');
                $mailing_state = $request->getParam('business_state');
                $mailing_postcode = $request->getParam('business_postcode');
            } else {
                $mailing_address = $request->getParam('mailing_address');
                $mailing_suburb = $request->getParam('mailing_suburb');
                $mailing_state = $request->getParam('mailing_state');
                $mailing_postcode = $request->getParam('mailing_postcode');
            }

            //GET MAX CUSTOMER ID
            $customer_id = $this->stakeholders->max('customer_id');
            $next_customer_id = $customer_id + 1;

            $stakeholder = $this->stakeholders->firstorcreate([
                'guid' => $guid,
                'customer_id' => $next_customer_id,
                'myob_integ_status' => 'N',
                'business_name' => $request->getParam('business_name'),
                'company_name' => $request->getParam('company_name'),
                'business_abn' => $request->getParam('business_abn'),
                'business_acn' => $request->getParam('business_acn'),
                'business_arbn' => $request->getParam('business_arbn'),
                'business_type' => $request->getParam('business_type'),
                'member_type' => $request->getParam('member_type'),
                'member_status' => $request->getParam('member_status'),
                'activity_type' => $request->getParam('activity_type'),
                'business_phone' => $request->getParam('business_phone'),
                'business_fax' => $request->getParam('business_fax'),
                'business_email' => $request->getParam('business_email'),
                'accounts_email' => $request->getParam('accounts_email'),
                'business_address' => $request->getParam('business_address'),
                'business_suburb' => $request->getParam('business_suburb'),
                'business_state' => $request->getParam('business_state'),
                'business_postcode' => $request->getParam('business_postcode'),
                'set_mailing_address' => $_SESSION['check_member_mailing'],
                'mailing_address' => $mailing_address,
                'mailing_suburb' => $mailing_suburb,
                'mailing_state' => $mailing_state,
                'mailing_postcode' => $mailing_postcode,
                'primary_contact' => 0,
                'row_version' => $row_version
            ]);

            $this->flash->addMessage('info', 'New Stakeholder has been added!');

            $return_array = array(
                'next' => 'ok',
                'update_id' => $stakeholder['id']
            );

            return json_encode($return_array);
        //}
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