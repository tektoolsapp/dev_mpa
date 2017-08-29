<?php

namespace App\Controllers\Transactions;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Models\Invoices;
use App\Models\Order;
use App\Models\OrdersProductsPivot;
use App\Models\Product;
use App\Models\Members;
use App\Pdf\PdfGeneration;
use App\Myob\MyobApi;
use App\Mail\EmailInvoice;
use App\Mail\Mailer\Mailer;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


use App\Validation\Forms\GetInvoicesForm;

class InvoicesController
{
    protected $router;
    protected $validator;
    protected $flash;
    protected $invoices;
    protected $mail;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash, Invoices $invoices, Mailer $mail)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->invoices = $invoices;
        $this->mail = $mail;
    }

    private function getInvoices($start, $end, Members $member){

        $invoices = $this->invoices
            ->where('invoice_date', '>=', Carbon::createFromFormat('d-m-Y H:i:s', $start . "00:00:00"))
            ->where('invoice_date', '<=', Carbon::createFromFormat('d-m-Y H:i:s', $end . "23:59:59"))
            ->orderBy('myob_id', 'asc')
            ->get();

        //SET PAGE DISPLAY TOTALS
        $invoice_total = $invoices->count();
        $invoice_count = count($invoices);

        foreach ($invoices as $invoice) {

            $customer_id = $invoice->customer_id;
            $customer_name = $member->where('customer_id', '=', $customer_id)
                ->first(['business_name']);
            $invoice->customer_name = $customer_name;

            $invoice_type = $invoice->invoice_type;
            if ($invoice_type == 'F') {
                $invoice_type_desc = 'Flimsy';
            }
            $invoice->invoice_type_desc = $invoice_type_desc;

            $invoice_status = $invoice->invoice_status;
            if ($invoice_status == 'N') {
                $invoice_status_desc = 'New Invoice';
            } elseif ($invoice_status == 'P') {
                $invoice_status_desc = 'Exported';
            }
            $invoice->invoice_status_desc = $invoice_status_desc;
        }

        $invoices = (object) array(
            'invoices' => $invoices,
            'invoice_total' => $invoice_total,
            'invoice_count' => $invoice_total
        );

        return $invoices;
    }

    public function invoiceError(Request $request, Response $response, Twig $view, Members $member)
    {
        $error_type = $request->getParam('error_type');

        if($error_type == 'N'){
            $error_msg = 'No Invoices selected for Export to MYOB';
        } else {
            $error_msg = 'Error Message not set!';
        }

        $flash_messages = array(
            "error" => $error_msg
        );

        $_SESSION['export_error'] = json_encode($flash_messages);

    }

    public function getInvoicesByCustomerName($name, Request $request, Response $response, Twig $view, Members $member){

        $_SESSION['get_customer_name'] = $name;

        //GET THE CUSIOMER ID FOR THE MEMBER NAME
        $member = $member->where('business_name', $name)->get(['customer_id'])->first();
        //GET THE INVOICES FOR THE CUSTOMER ID
        $invoices = $this->invoices
            ->where('customer_id', '=', $member->customer_id)
            ->orderBy('myob_id', 'asc')
            ->get();

        //SET PAGE DISPLAY TOTALS
        $invoice_total = $invoices->count();
        $invoice_count = count($invoices);

        foreach ($invoices as $invoice) {

            $customer_id = $invoice->customer_id;
            $customer_name = $member->where('customer_id', '=', $customer_id)
                ->first(['business_name']);
            $invoice->customer_name = $customer_name;

            $invoice_type = $invoice->invoice_type;
            if ($invoice_type == 'F') {
                $invoice_type_desc = 'Flimsy';
            }
            $invoice->invoice_type_desc = $invoice_type_desc;

            $invoice_status = $invoice->invoice_status;
            if ($invoice_status == 'N') {
                $invoice_status_desc = 'New Invoice';
            } elseif ($invoice_status == 'P') {
                $invoice_status_desc = 'Exported';
            }
            $invoice->invoice_status_desc = $invoice_status_desc;
        }

        //if ($start && $end && sizeof($invoices->invoices) < 1) {
        if (sizeof($invoices) < 1) {
            $flash_messages = array(
                "error" => 'No Invoices for the Customer selected - '.$_SESSION['get_customer_name']
            );
        } else {
            $flash_messages = array();
        }

        return $view->render($response, 'transactions/invoices.index.twig', [
            //'start' => $start,
            //'end' => $end,
            'flash' => $flash_messages,
            'invoice_customer_name' =>  $_SESSION['get_customer_name'],
            'invoices' => $invoices,
            'invoice_total' => $invoice_total,
            'invoice_count' => $invoice_count,
            'js_script' => 'invoices',
            'integ_connect_script' => 'myob_connect',
            'integ_process_script_1' => 'myob_customers',
            'integ_process_script_2' => 'myob_invoices'
        ]);
    }

    public function getInvoicesByDate(Request $request, Response $response, Twig $view, Flash $flash, Members $member)
    {
        unset($_SESSION['get_customer_name']);

        $validation = $this->validator->validate($request, GetInvoicesForm::rules());

        if ($validation->fails()) {
            return $response->withRedirect($this->router->pathFor('invoices.index'));
        } else {

            //SET THE START DATE
            $start = $request->getParam('from_date');
            $_SESSION['from_date'] = $start;
            //SET THE END DATE
            $end = $request->getParam('to_date');
            $_SESSION['to_date'] = $end;

            //dump("START: ", $start);
            //dump("END: ", $end);

            if (!empty($start) && !empty($end)) {

                $invoices = $this->getInvoices($start, $end, $member);

                if ($start && $end && sizeof($invoices->invoices) < 1) {
                    $flash_messages = array(
                        "error" => 'No Invoices for the range selected'
                    );
                } else {
                    $flash_messages = array();
                }

                return $view->render($response, 'transactions/invoices.index.twig', [
                    'start' => $start,
                    'end' => $end,
                    'flash' => $flash_messages,
                    'invoices' => $invoices->invoices,
                    'invoice_total' => $invoices->invoice_total,
                    'invoice_count' => $invoices->invoice_count,
                    'js_script' => 'invoices',
                    'integ_connect_script' => 'myob_connect',
                    'integ_process_script_1' => 'myob_customers',
                    'integ_process_script_2' => 'myob_invoices'
                ]);
            }
        }
    }

    public function index(Request $request, Response $response, Twig $view, Flash $flash, Members $member)
    {
        unset($_SESSION['get_customer_name']);

        //SET THE START DATE
        if(isset($_SESSION['from_date'])){
            $start = $_SESSION['from_date'];
        } else {
            $start = Carbon::now()->startOfMonth()->format('d-m-Y');
            $_SESSION['from_date'] = $start;
        }

        //SET THE END DATE
        if(isset($_SESSION['to_date'])){
            $end = $_SESSION['to_date'];
        } else {
            $end = Carbon::now()->endOfMonth()->format('d-m-Y');
            $_SESSION['to_date'] = $end;
        }

        if(!empty($start) && !empty($end)) {

            $invoices = $this->getInvoices($start, $end, $member);

            if ($start && $end && sizeof($invoices) < 1) {
                $flash_messages = array(
                    "error" => 'No Invoices for the range selected'
                );
            } else {
                $flash_messages = array();
            }

            if(isset($_SESSION['export_error'])){
                $error_message = json_decode($_SESSION['export_error']);

                $flash_messages = array(
                    "error" => $error_message->error
                );

            }
            unset($_SESSION['export_error']);

            return $view->render($response, 'transactions/invoices.index.twig', [
                'start' => $start,
                'end' => $end,
                'flash' => $flash_messages,
                'invoices' => $invoices->invoices,
                'invoice_total' => $invoices->invoice_total,
                'invoice_count' => $invoices->invoice_count,
                'js_script' => 'invoices',
                'integ_connect_script' => 'myob_connect',
                'integ_process_script_1' => 'myob_customers',
                'integ_process_script_2' => 'myob_invoices'
            ]);

        }
    }

    public function prepExport($id, Request $request, Response $response, Order $order, OrdersProductsPivot $products, Product $desc, Members $member)
    {
        $invoice = $this->invoices->where('id', $id)->get()->first();

        $myob_uid = $invoice->myob_uid;
        $myob_row_version = $invoice->myob_row_version;
        $customer_id = $invoice->customer_id;

        $customer = $member->where('id', $customer_id)->get()->first();

        $business_address = $customer->business_address;
        $business_suburb = $customer->business_suburb;
        $business_state = $customer->business_state;
        $business_postcode = $customer->business_postcode;
        $customer_address = stripslashes($business_address." ".$business_suburb." ".$business_state." ".$business_postcode);

        //dump($customer);
        //die();

        //GET THE ORDER RELATED TO THE INVOICE
        $order = $order->where('id', $invoice->order_id)->get()->first();
        //GET THE PRODUCT ITEMS RELATED TO THE ORDER
        $items = $products->where('order_id', $order->id)->get();
        //GET THE PRODUCT DETAILS FOR THE PRODUCT ITEM

        $invoice_total = 0;
        $lines_array = array();
        $gl_sales_uid = getenv('API_INCOME_ACCOUNT');
        $lines_account_array = array();
        $lines_account_array['UID'] = $gl_sales_uid;
        $lines_taxcode_array = array();
        //if(!empty($this_customer_tax_uid)){
        $lines_taxcode_array['UID'] = getenv('API_GST_TAXCODE');
        //} else {
        //$lines_taxcode_array['UID'] = $gst_tax_rate_uid;
        //}

        //$header_desc_array = explode("</p>", $this_invoice_desc);

        //$header_desc_array = explode("</tr>", $this_invoice_desc);

        //loop header desc array and strip tags
        //$use_header_lines = array();
        //for($h = 0; $h < sizeof($header_desc_array); $h++) {
        //$clean_desc = strip_tags($header_desc_array[$h]);
        $clean_desc = $invoice->invoice_description;
        $clean_desc = preg_replace('%\\\\/%sm', '/', $clean_desc);
        $clean_desc = preg_replace('/\\\\"/sm', '"', $clean_desc);
        $clean_desc = preg_replace('/[\r\n]/sm', '', $clean_desc);
        $clean_desc = str_replace('&nbsp;', '', $clean_desc);
        $clean_desc = str_replace('&amp;', '&', $clean_desc);
        $clean_desc = str_replace('&quot;', '\"', $clean_desc);
        $clean_desc = str_replace('\'', '', $clean_desc);
        $clean_desc = str_replace('\\', '', $clean_desc);
        //remove spaces whilst leaving word gaps
        $clean_desc = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $clean_desc)));

        /*
        if(!empty($clean_desc)) {
            $use_header_lines[] = $clean_desc;
        }
        */
        //}

        //print_r($use_header_lines);

        //$this_desc = substr($use_header_lines[0],0,255);
        $this_desc = substr($clean_desc, 0, 255);

        array_push($lines_array, array("Type" => "Header", "Description" => $this_desc));

        foreach ($items as $item) {
            $desc = $desc->where('product_id', $item->product_id)->get()->first();
            //ADD THE PRODUCT DETAILS TO THE PRODUCT ITEM
            $item->slug = $desc->slug;
            $item->stock = $desc->stock;
            $item->product_desc = $desc->description;
            $item->price = $desc->price;
            $item->sub_total = round($item->quantity * $desc->price, 2);

            array_push($lines_array, array(
                "Type" => "Transaction",
                "Description" => substr($item->product_desc, 0, 255),
                "Total" => $item->sub_total,
                "Account" => $lines_account_array,
                //"Job" => $lines_job_array,
                "TaxCode" => $lines_taxcode_array
            ));

            $invoice_total = $invoice_total + $item->sub_total;
        }

        $gst = round($invoice_total * 10 / 100, 2);
        $grand_total = $invoice_total + $gst;

        //ADD THE ORDER ITEM TO THE INVOICE
        $invoice->items = $items;
        $invoice->total = $invoice_total;
        $invoice->gst = $gst;
        $invoice->grand_total = $grand_total;

        //PREP INVOICE DETAILS FOR EXPORT
        //BUILD CUSTOMER ARRAY
        $customer_array = array();
        $customer_array['UID'] = $customer->myob_uid;

        $payment_terms_array = array();
        $customer_payment_terms = $invoice->payment_terms;

        if($customer_payment_terms == '7_days') {
            $payment_is_due = 'InAGivenNumberOfDays';
            $num_days = 7;
        } elseif($customer_payment_terms == '14_days') {
            $payment_is_due = 'InAGivenNumberOfDays';
            $num_days = 14;
        } elseif($customer_payment_terms == '30_days') {
            $payment_is_due = 'InAGivenNumberOfDays';
            $num_days = 30;
        } elseif($customer_payment_terms == '45_days') {
            $payment_is_due = 'InAGivenNumberOfDays';
            $num_days = 45;
        } elseif($customer_payment_terms == '60_days') {
            $payment_is_due = 'InAGivenNumberOfDays';
            $num_days = 60;
        } elseif($customer_payment_terms == '7_net') {
            $payment_is_due = 'NumberOfDaysAfterEOM';
            $num_days = 7;
        } elseif($customer_payment_terms == '14_net') {
            $payment_is_due = 'NumberOfDaysAfterEOM';
            $num_days = 14;
        } elseif($customer_payment_terms == '30_net') {
            $payment_is_due = 'NumberOfDaysAfterEOM';
            $num_days = 30;
        } elseif($customer_payment_terms == '45_net') {
            $payment_is_due = 'NumberOfDaysAfterEOM';
            $num_days = 45;
        } elseif($customer_payment_terms == '60_net') {
            $payment_is_due = 'NumberOfDaysAfterEOM';
            $num_days = 60;
        } elseif($customer_payment_terms == 'pre_pay') {
            $payment_is_due = 'PrePaid';
            $num_days = 0;
        } elseif($customer_payment_terms == 'c_o_d') {
            $payment_is_due = 'CashOnDelivery';
            $num_days = 0;
        } else {
            $payment_is_due = 'CashOnDelivery';
            $num_days = 0;
        }

        $payment_terms_array['PaymentIsDue'] = $payment_is_due;
        $payment_terms_array['BalanceDueDate'] = $num_days;

        $posting_array = array();

        $use_ship_to_address = 'Y';
        $this_invoice_gst_excluded = 'Y';

        if ($this_invoice_gst_excluded == 'Y') {
            $tax_inclusive = 'false';
        } else {
            $tax_inclusive = 'true';
        }

        $this_memo = "memo";

        //format the invoice date to ISO 601
        $this_invoice_date = $invoice->invoice_date;
        $datetime = new \DateTime($this_invoice_date);
        $date = $datetime->format('c');
        $date = substr($date, 0, -6);
        $date .= ".000";

        if(!empty($myob_uid)){
            $posting_array['UID'] = $myob_uid;
        }
        $posting_array['Date'] = $date;
        $posting_array['CustomerPurchaseOrderNumber'] = $invoice->order_id;
        $posting_array['Customer'] = $customer_array;
        $posting_array['Terms'] = $payment_terms_array;
        $posting_array['Lines'] = $lines_array;
        if ($use_ship_to_address == 'Y') {
            $posting_array['ShipToAddress'] = $customer_address;
        } else {
            $posting_array['ShipToAddress'] = '';
        }
        $posting_array['IsTaxInclusive'] = $tax_inclusive;
        $posting_array['JournalMemo'] = $this_memo;
        $posting_array['InvoiceDeliveryStatus'] = 'Nothing';

        if(!empty($myob_uid)){
            $posting_array['RowVersion'] = $myob_row_version;
        }

        //RETURN INVOICE EXPORT PAYLOAD;
        $json_update = json_encode($posting_array, JSON_HEX_APOS);

        return $json_update;
    }

    public function export(Request $request, Response $response, MyobApi $myob)
    {
        $type = $request->getParam('type');
        $myob_UID = $request->getParam('UID');
        $payload = $request->getParam('payload');
        $api_url = getenv('API_URL');
        $api_company_file = getenv('API_COMPANY_FILE');
        $api_key = getenv('API_KEY');
        $api_coy_un = getenv('API_COY_UN');
        $api_coy_pw = getenv('API_COY_PW');

        if($type == 'add') {
            $filter_pre = '/Sale/Invoice/Service?returnBody=true';
        } else {
            $filter_pre = '/Sale/Invoice/Service/'.$myob_UID.'?returnBody=true';
        }

        $url_extension = $filter_pre;
        $this_url = $api_url . $api_company_file . $url_extension;

        if($type == 'add') {
            return $myob->postURL($this_url, $payload, '', '', $api_key, $api_coy_un, $api_coy_pw);
        } else {
            return $myob->putURL($this_url, $payload, '', '', $api_key, $api_coy_un, $api_coy_pw);
        }
    }

    public function exportUpdate(Request $request, Response $response, PdfGeneration $pdf)
    {
        $invoice_id = $request->getParam('id');
        $this->invoices->where('id', $invoice_id)
            ->update([
                'myob_uid' => $request->getParam('UID'),
                'myob_id' => $request->getParam('num'),
                'myob_row_version' => $request->getParam('row_version'),
                'invoice_status' => 'P'
            ]);

        $update_type = $request->getParam('update_type');

        //CREATE/UPDATE THE PDF

        $customer_dets = $this->invoices->where('id', $invoice_id)->first(['customer_id']);
        $customer_id = $customer_dets->customer_id;

        $content = $pdf->flimsyInvoice($invoice_id, $request->getParam('num'));

        $mpdf = new \Mpdf\mPDF();
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->WriteHTML($content->header);
        $mpdf->WriteHTML($content->body);
        $mpdf->SetHTMLFooter($content->footer);
        $file_output = "invoice_pdfs/invoice_flimsy_".$customer_id."_".$invoice_id.".pdf";

        $mpdf->Output($file_output, 'F');

        if($update_type == 'add') {
            $msg = "Invoice was successfully Exported to MYOB";
        } else {
            $msg = "Invoice was successfully Updated in MYOB";
        }

        $this->flash->addMessage('success', $msg);
    }

    public function emailinvoice(Request $request, Response $response, Members $member)
    {
        $customer_id = 177;
        $member = $member->where('customer_id', $customer_id)->get()->first();
        $this->mail->to($member->accounts_email, $member->business_name)->send(new EmailInvoice($member));
        $this->flash->addMessage('success', "Emailed");

        return $response->withRedirect($this->router->pathFor('invoices.index'));
    }

    public function getUpdate($id, Request $request, Response $response, Twig $view, Order $order, OrdersProductsPivot $products, Product $desc)
    {
        $invoice = $this->invoices->where('id', $id)->get()->first();

        dump($invoice);

        //GET THE ORDER RELATED TO THE INVOICE
        $order = $order->where('id', $invoice->order_id)->get()->first();
        //GET THE PRODUCT ITEMS RELATED TO THE ORDER
        $items = $products->where('order_id', $order->id)->get();
        //GET THE PRODUCT DETAILS FOR THE PRODUCT ITEM

        $invoice_total = 0;

        foreach ($items as $item) {
            $desc = $desc->where('product_id', $item->product_id)->get()->first();
            //ADD THE PRODUCT DETAILS TO THE PRODUCT ITEM
            $item->slug = $desc->slug;
            $item->stock = $desc->stock;
            $item->product_desc = $desc->description;
            $item->price = $desc->price;
            $item->sub_total = $item->quantity * $desc->price;

            $invoice_total = $invoice_total + $item->sub_total;
        }

        $gst = round($invoice_total * 10/100,2);
        $grand_total = $invoice_total + $gst;

        //ADD THE ORDER ITEM TO THE INVOICE
        $invoice->items = $items;
        $invoice->total = $invoice_total;
        $invoice->gst = $gst;
        $invoice->grand_total = $grand_total;

        return $view->render($response, 'transactions/invoice.update.twig', [
            'invoice' => $invoice,
            'mode' => 'edit',
            'js_script' => 'invoice',
            'integ_connect_script' => 'myob_connect',
            'integ_process_script_1' => 'myob_customers',
            'integ_process_script_2' => 'myob_invoices'
        ]);
    }

    public function invoice(Request $request, Response $response, Twig $view, Flash $flash)
    {
        unset($_SESSION['myob_customer_updates']);

        return $view->render($response, 'events/invoice.twig', [
            'js_script' => 'invoice',
            'integ_connect_script' => 'myob_connect',
            'integ_process_script_1' => 'myob_customers',
            'integ_process_script_2' => 'myob_invoices'
        ]);
    }

    /*
    public function pdf(PdfGeneration $pdf)
    {
        $content = $pdf->invoice();

        //dump($content->header);
        //die();

        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($content->header);
        $mpdf->WriteHTML($content->firstletter);
        //$mpdf->WriteHTML($letter);
        //$mpdf->WriteHTML($letter);
        $file_output = "invoice_pdfs/test.pdf";
        $mpdf->Output($file_output, 'F');
    }
    */
}