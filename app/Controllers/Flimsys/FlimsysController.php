<?php

namespace App\Controllers\Flimsys;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Forms\FlimsysForm;
use App\Models\Flimsys;
use App\Models\Members;
use App\Models\Contacts;
use App\Models\Product;
use Slim\Csrf\Guard;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\MyOrders\ProcessOrders;
use App\Models\Order;
use App\Models\Invoices;
use App\Pdf\PdfGeneration;

class FlimsysController
{
    protected $router;
    protected $view;
    protected $validator;
    protected $flash;
    protected $contacts;
    protected $members;
    protected $flimsys;

    public function __construct(Router $router, Twig $view, ValidatorInterface $validator, Flash $flash, Contacts $contacts, Members $members, Flimsys $flimsys)
    {
        $this->router = $router;
        $this->view = $view;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->contacts = $contacts;
        $this->members = $members;
        $this->flimsys = $flimsys;
    }

    public function index(Request $request, Response $response)
    {
        $flimsys = $this->flimsys->all();

        foreach($flimsys as $flimsy){
            $member_id = $flimsy->customer_id;
            $member = $this->members->where('id', '=', $member_id)
                ->first(['business_name', 'business_address']);
            $flimsy['entity_name'] = $member->business_name;
            $status = $flimsy->status;
            if($status == 'N'){
                $status_desc = 'New Request';
            }
            $flimsy['status_desc'] = $status_desc;

            $payment_method = $flimsy->payment_method;
            if($payment_method == 'C'){
                $payment_method_desc = 'Credit';
            }
            $flimsy['payment_method_desc'] = $payment_method_desc;

            $payment_status = $flimsy->payment_status;
            if($payment_status == 'U'){
                $payment_status_desc = 'Unpaid (credit)';
            } elseif($payment_status == 'I'){
                $payment_status_desc = 'Invoiced';
            }
            $flimsy['payment_status_desc'] = $payment_status_desc;
        }

        return $this->view->render($response, 'flimsys/flimsys.index.twig', [
            'flimsys' => $flimsys,
            'js_script' => 'flimsys'
        ]);
    }

    public function newRequest(Request $request, Response $response)
    {
        return $this->view->render($response, 'flimsys/flimsy.update.twig', [
            'mode' => 'add',
            'js_script' => 'flimsys',
        ]);
    }

    public function add(Request $request, Response $response)
    {
        $_SESSION['order_flimsy'] = $request->getParam('checkbox_order_flimsy');
        $_SESSION['order_sewer_junction'] = $request->getParam('checkbox_order_sewer_junction');
        $_SESSION['order_water_main'] = $request->getParam('checkbox_order_water_main');

        $validation = $this->validator->validate($request, FlimsysForm::rules());

        if ($validation->fails()) {
            //return $response->withRedirect($this->router->pathFor('flimsys.index'));
            return 'errors';
        } else {
            $posting_array = $request->getParams();
            $order_types = array();

            foreach ($posting_array as $key => $value) {
                if (substr($key, 0, 8) == 'checkbox') {
                    $order_types[$key] = $value;
                }
            }

            $post_order_types = json_encode($order_types);
            $guid = $this->GUID();
            $row_version = $this->uniqidReal();
            $logged_in_user = getenv('LOGGED_IN_USER');
            $request_date_time = new \DateTime('now');

            //dump($request->getParam('customer_id'));
            //dump($request->getParams());
            //dump((int)$request->getParam('customer_id'));

            //die();

            $customer_type = 'M';

            $member = $this->flimsys->firstorcreate([
                'guid' => $guid,
                'operator' => $logged_in_user,
                'customer_type' => $customer_type,
                'customer_id' => (int)$request->getParam('customer_id'),
                'ordered_by' => $request->getParam('ordered_by'),
                'request_datetime' => $request_date_time,
                'discount_pricing' => $request->getParam('discount_pricing'),
                'order_po' => $request->getParam('order_po'),
                'order_method' => $request->getParam('order_method'),
                'payment_method' => $request->getParam('payment_method'),
                'order_type' => $post_order_types,
                'contours' => $request->getParam('contours'),
                'lot_num' => $request->getParam('lot_num'),
                'house_num' => $request->getParam('house_num'),
                'street_name' => $request->getParam('street_name'),
                'suburb' => $request->getParam('suburb'),
                'postcode' => $request->getParam('postcode'),
                'closest_cross_street' => $request->getParam('closest_cross_street'),
                'send_by' => $request->getParam('send_by'),
                'order_total' => $request->getParam('order_total'),
                'status' => $request->getParam('flimsy_status'),
                'payment_status' => 'U',
                'district'=> $request->getParam('district'),
                'field_book'=> $request->getParam('field_book'),
                'page_num' => $request->getParam('page_num'),
                'extra_pages' => $request->getParam('extra_pages'),
                'row_version' => $row_version
            ]);

            $this->flash->addMessage('info', 'New Flimsy Request has been added!');

            //return $response->withRedirect($this->router->pathFor('members.index', ['filter' => 'all']));\
            //dump($member);
            //die();

            /*
            $return_array = array(
                'next' => 'ok',
                'update_id' => $member['id']
            );

            return json_encode($return_array);
            */
        }
    }

    public function get($id, $source, $status, Request $request, Response $response)
    {
        $flimsy = $this->flimsys->where('id', $id)->get()->first();
        //SET THE UPDATE SOURCE
        $flimsy->source = $source;
        $order_types = $flimsy->order_type;
        $order_type_array = json_decode($order_types);

        $customer_type = $flimsy->customer_type;//NEEDED WHEN NON-MEMBERS ADDED
        $customer_id = $flimsy->customer_id;
        //GET THE MEMBER DETAILS
        $member = $this->members->where('id', '=', $customer_id)
            ->first(['customer_id', 'business_name', 'business_address', 'business_suburb', 'business_state', 'business_postcode', 'accounts_email', 'payment_method']);

        //GET THE ORDERED BY CONTACT DETAILS
        $ordered_by = $flimsy->ordered_by;
        $order_contact = $this->contacts->where('id', '=', $ordered_by)
            ->first(['firstname', 'surname', 'phone', 'email']);

        //GET ALL THE CONTACTS FOR THE MEMBER
        $contacts = $this->members->find($customer_id)->contacts()
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

        return $this->view->render($response, 'flimsys/flimsy.update.twig', [
            'mode' => 'edit',
            'flimsy' => $flimsy,
            'member' => $member,
            'flimsy_invoice_customer' => $_SESSION['get_customer_name'],
            'contacts' => $contacts,
            'order_contact' => $order_contact,
            'order_types' => $order_type_array,
            'js_script' => 'flimsys',
        ]);
    }

    public function getMemberByName($name, Request $request, Response $response)
    {
        $member_details = array();
        $member = $this->members->getMemberByName($name);
        $member_id = $member->id;

        //GET ALL THE CONTACTS FOR THE MEMBER
        $contacts = $this->members->find($member_id)->contacts()
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

        $member_details['member'] = $member;
        $member_details['contacts'] = $contacts;

        return $response->withJson($member_details, 200);
    }

    public function getContactById($id, Request $request, Response $response)
    {
        //GET ALL THE CONTACTS FOR THE MEMBER
        $contact = $this->contacts->where('id', '=', $id)
            ->first(['firstname', 'surname', 'phone', 'email']);

        return $response->withJson($contact, 200);
    }

    public function edit($id, Request $request, Response $response)
    {
        $_SESSION['order_flimsy'] = $request->getParam('checkbox_order_flimsy');
        $_SESSION['order_sewer_junction'] = $request->getParam('checkbox_order_sewer_junction');
        $_SESSION['order_water_main'] = $request->getParam('checkbox_order_water_main');

        $validation = $this->validator->validate($request, FlimsysForm::rules());

        if ($validation->fails()) {
            //return $response->withRedirect($this->router->pathFor('member.edit', ['id' => $id]));
            return 'errors';
        } else {
            $posting_array = $request->getParams();
            $order_types = array();

            foreach ($posting_array as $key => $value) {
                if (substr($key, 0, 8) == 'checkbox') {
                    $order_types[$key] = $value;
                }
            }

            $post_order_types = json_encode($order_types);
            $row_version = $this->uniqidReal();
            //$logged_in_user = getenv('LOGGED_IN_USER');
            $process_date_time = new \DateTime('now');

            $this->flimsys->where('id', $id)
                ->update([
                    //'customer_type' => $request->getParam('customer_type'),
                    'customer_id' => $request->getParam('customer_id'),
                    'ordered_by' => $request->getParam('ordered_by'),
                    'process_datetime' => $process_date_time,
                    'discount_pricing' => $request->getParam('discount_pricing'),
                    'order_po' => $request->getParam('order_po'),
                    'order_method' => $request->getParam('order_method'),
                    'payment_method' => $request->getParam('payment_method'),
                    'order_type' => $post_order_types,
                    'contours' => $request->getParam('contours'),
                    'lot_num' => $request->getParam('lot_num'),
                    'house_num' => $request->getParam('house_num'),
                    'street_name' => $request->getParam('street_name'),
                    'suburb' => $request->getParam('suburb'),
                    'postcode' => $request->getParam('postcode'),
                    'closest_cross_street' => $request->getParam('closest_cross_street'),
                    'send_by' => $request->getParam('send_by'),
                    'order_total' => $request->getParam('order_total'),
                    'status' => $request->getParam('flimsy_status'),
                    //'payment_status' => 'U',
                    'district'=> $request->getParam('district'),
                    'field_book'=> $request->getParam('field_book'),
                    'page_num' => $request->getParam('page_num'),
                    'extra_pages' => $request->getParam('extra_pages'),
                    'row_version' => $row_version
                ]);

            $this->flash->addMessage('info', 'Flimsy details were successfully updated!');

            /*
            $return_array = array(
                'next' => 'ok',
                'update_id' => $request->getParam('member_id')
            );

            return json_encode($return_array);
            */
        }
    }

    public function orderFlimsy($id, Request $request, Response $response, ProcessOrders $process_orders, Order $order, Product $product, Invoices $invoice, PdfGeneration $pdf)
    {
        $flimsy = $this->flimsys->where('id', '=', $id)
            ->first();
        $customer_id = $flimsy->customer_id;
        $customer_reference = $flimsy->order_po;
        $order_items = json_decode($flimsy->order_type);
        $payment_status = $flimsy->payment_status;

        //BUILD THE PRODUCTS/ORDERS PIVOT UPDATE
        $product_ids = [];
        $quantities = [];

        foreach ($order_items as $key => $value) {
            $product_ids[] = $value;
            $quantities[] = array("quantity" => 1);
        }
        $products = $product->find($product_ids);

        if($payment_status == 'U') {
            //("add");
            //CREATE AN ORDER
            $hash = bin2hex(openssl_random_pseudo_bytes(32));
            $order = $order->create([
                'hash' => $hash,
                'paid' => false,
                'total' => $flimsy->order_total,
                'address_id' => 1,
                'customer_type' => $flimsy->customer_type,
                'customer_id' => $flimsy->customer_id,
            ]);

            $order->products()->saveMany(
                $products,
                $quantities
            );

            $invoice_id = $invoice->max('id');
            $next_invoice_id = $invoice_id + 1;
            $payment_terms = '7_days';
            //CREATE THE INVOICE
            $invoice = $invoice->create([
                'order_id' => $order->id,
                'invoice_id' => $next_invoice_id,
                'invoice_type' => 'F',
                'invoice_ref' => $id,
                'payment_terms' => $payment_terms,
                'customer_id' => $customer_id,
                'customer_reference' => $customer_reference,
                'invoice_description' => 'Flimsy Request',
                'invoice_status' => 'N',
            ]);

            $invoice_id = $invoice->id;

            //UPDATE FLIMSY INVOICE DETAILS
            $this->flimsys->where('id', $id)
                ->update([
                    'payment_status' => 'I',
                    'order_id' => $order->id,
                    'invoice_id' => $invoice_id
                ]);

        } else {
            //UPDATE THE ORDER
            $order_update_id = $flimsy->order_id;
            //dump("ORDER_ID: ", $order_update_id);

            $order_to_update = $order->find($order_update_id);

            //FIRST REMOVE THE GROUP FROM THE PIVOT TABLE
            $order_to_update->products()->detach();

            //ADD THE UPDATED ITEMS
            //$order_to_update->products()->updateExistingPivot($products, $quantities);
            $order_to_update->products()->saveMany(
                $products,
                $quantities
            );
            $invoice_id = $flimsy->invoice_id;

            $this->flash->addMessage('info', 'Flimsy Invoice details were successfully updated!');
        }

        //CREATE/UPDATE THE PDF

        //SET THE MYOB INVOICE NUMBER IN THE PDF IF IT EXISTS
        $invoice_dets = $invoice->where('order_id', '=', $order_update_id)
            ->first(['myob_id']);

        //dump("DETS: ", $invoice_dets);

        $myob_invoice = $invoice_dets->myob_id;
        //dump("MYOB INV: ", $myob_invoice);

        $content = $pdf->flimsyInvoice($invoice_id, $myob_invoice);

            $mode = '';
			$format = '';
			$default_font_size = '';
			$default_font = '';
			$mgl = 100;
			$mgr = 100;
			$mgt = 50;
			$mgb = '';
			$mgh = 400;
			$mgf= '';
			$orientation = '';

        $config_array = array(
            $mode,
			$format,
			$default_font_size,
			$default_font,
			$mgl,
			$mgr,
			$mgt,
			$mgb,
			$mgh,
			$mgf,
			$orientation
        );

        $mpdf = new \Mpdf\mPDF();
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->WriteHTML($content->header);
        $mpdf->WriteHTML($content->body);
        $mpdf->SetHTMLFooter($content->footer);
        $file_output = "invoice_pdfs/invoice_flimsy_".$customer_id."_".$invoice_id.".pdf";

        $mpdf->Output($file_output, 'F');

        //return $response->withJson($flimsy, 200);

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

    /*
    public function upload(Request $request, Response $response, Router $router, Twig $view, Guard $guard, Flash $flash, Asset $asset )
    {
        $files = $request->getUploadedFiles();
        $upload_item = $files['upload_item'];
        $uploadFilename = $upload_item->getClientFilename();

        //dump($uploadFilename);

        //die();

        if($uploadFilename) {

            if ($upload_item->getError() === UPLOAD_ERR_OK) {

                $upload_item->moveTo("assets/images/" . $uploadFilename);
                $imagepath = "assets/images/" . $uploadFilename;

                $asset = $asset->firstorcreate([
                    'file_name' => $imagepath
                ]);

                $flash->addMessage('success', "File ".$uploadFilename. " successfully uploaded!");

            }
        } else {

            $flash->addMessage('error', "No File Selected for Upload");
        }

        return $response->withRedirect($router->pathFor('flimsys.index'));

    }
    */

}