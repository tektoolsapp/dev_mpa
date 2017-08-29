<?php

namespace App\Pdf;

use App\Models\Flimsys;
use App\Models\Members;
use App\Models\Contacts;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoices;
use App\Models\OrdersProductsPivot;

class PdfGeneration
{
    protected $invoices;
    protected $members;
    protected $product;
    protected $order;
    protected $orderProducts;

    public function __construct(Invoices $invoices, Members $members, Product $product, OrdersProductsPivot $orderProducts, Order $order)
    {
        $this->members = $members;
        $this->invoices = $invoices;
        $this->product = $product;
        $this->orderProducts = $orderProducts;
        $this->order = $order;
    }

    public function flimsyInvoice($invoice, $myob_invoice=null)
    {
        $invoice_dets = $this->invoices->where('id', $invoice)->get()->first();

        if (!empty($myob_invoice)) {
            $invoice_num = ltrim($myob_invoice, '0');
        } else {
            $invoice_num = $invoice_dets->id;
        }

        $customer_dets = $this->members->where([
            ['customer_id', '=', $invoice_dets->customer_id],
        ])->get()->first();

        $business_address = $customer_dets->business_address;
        $business_suburb = $customer_dets->business_suburb;
        $business_state = $customer_dets->business_state;
        $business_postcode = $customer_dets->business_postcode;

        $customer_name = stripslashes($customer_dets->business_name);
        $customer_address_1 = stripslashes($business_address);
        $customer_address_2 = stripslashes($business_suburb." ".$business_state." ".$business_postcode);

        //DEFAULT SHIP TO NAME/ADDRESS
        $st_customer_name = $customer_name;
        $st_customer_address_1 = $customer_address_1;
        $st_customer_address_2 = $customer_address_2;

        $client_reference = $invoice_dets->customer_reference;
        $invoice_date = $invoice_dets->invoice_date;
        $this_invoice_date = strtotime($invoice_date);
        $payment_terms_code = $invoice_dets->payment_terms;

        if($payment_terms_code == '7_days') {
            $payment_terms = 'NET 7 DAYS';
            $payment_date = strtotime("+7 days", $this_invoice_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '14_days') {
            $payment_terms = 'NET 14 DAYS';
            $payment_date = strtotime("+14 days", $this_invoice_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '30_days') {
            $payment_terms = 'NET 30 DAYS';
            $payment_date = strtotime("+30 days", $this_invoice_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif ($payment_terms_code == '45_days') {
            $payment_terms = 'NET 45 DAYS';
            $payment_date = strtotime("+45 days", $this_invoice_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '60_days') {
            $payment_terms = 'NET 60 DAYS';
            $payment_date = strtotime("+60 days", $this_invoice_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '7_net') {
            $payment_terms = 'NET 7 DAYS after EOM';
            $payment_date = strtotime("+7 days", $use_eom_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '14_net') {
            $payment_terms = 'NET 14 DAYS after EOM';
            $payment_date = strtotime("+14 days", $use_eom_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '30_net') {
            $payment_terms = 'NET 30 DAYS after EOM';
            $payment_date = strtotime("+30 days", $use_eom_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '45_net') {
            $payment_terms = 'NET 45 DAYS after EOM';
            $payment_date = strtotime("+45 days", $use_eom_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == '60_net') {
            $payment_terms = 'NET 60 DAYS after EOM';
            $payment_date = strtotime("+60 days", $use_eom_date);
            $payment_date_display = date('d-m-Y',$payment_date);
        } elseif($payment_terms_code == 'pre_pay') {
            $payment_terms = 'Pre-paid';
            $payment_date_display = 'NA';
        } elseif($payment_terms_code == 'c_o_d') {
            $payment_terms = 'C.O.D';
            $payment_date_display = 'NA';
        } elseif($payment_terms_code == 'immed') {
            $payment_terms = 'Immediately';
            $payment_date_display = 'NA';
        } elseif($payment_terms_code == 'pre_install') {
            $payment_terms = 'Due Before Install';
            $payment_date_display = 'NA';
        } else {
            $payment_terms = 'C.O.D';
            $payment_date_display = 'NA';
        }

        //GET THE ORDER RELATED TO THE INVOICE
        $order = $this->order->where('id', $invoice_dets->order_id)->get()->first();
        //GET THE PRODUCT ITEMS RELATED TO THE ORDER
        $items = $this->orderProducts->where('order_id', $order->id)->get();
        //GET THE PRODUCT DETAILS FOR THE PRODUCT ITEM

        $invoice_total = 0;

        foreach ($items as $item) {
            $desc = $this->product->where('product_id', $item->product_id)->get()->first();
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
        $invoice_dets->items = $items;
        $invoice_dets->total = $invoice_total;
        $invoice_dets->gst = $gst;
        $invoice_dets->grand_total = $grand_total;

        $header = '
            <html>
            <head>
            <style scoped>
                table {
                    font-family:Arial;
                    font-size:20px;
                    background-color:#FFF;
                }
                @page {
                    margin-top: 2cm;
                    margin-bottom: 2cm;
                    margin-left: 1.5cm;
                    margin-right: 1.5cm;
                    footer: html_letterfooter2;
                    background-color: #FFF;
                }
                @page :first {
                    /*marks: crop;*/
                    margin-top: 9.3cm;
                    margin-bottom: 4cm;
                    header: html_letterheader;
                    footer: html_letterfooter2;
                    /*footer: _blank;*/
                    /*footer: myfooter;*/
                    resetpagenum: 1;
                    background-color: #FFF;
                }
             </style>
             </head>
             <body>
             <htmlpageheader name="letterheader">
                    <table border="0" style="padding:0;" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="vertical-align:top;">
                                <table border="0" style="padding:0;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="width:230px;border:0px solid red;"><img width="210" src="images/logo.png" /></td>
                                        <td style="width:450px;padding:20px 0 0 0;border:0px solid red;vertical-align:top;">The Master Plumbers & Gas Fitters
                                            <br>Association of WA (Union of Employees)
                                            <br>Level 3, 353 Shepperton Road
                                            <br>East Victoria Park, WA 6101
                                            <br>ABN 13 804 458 187
                                        </td>
                                        <td style="width:350px;border:0px solid green;vertical-align:top;padding:20px 0 0 0;">Tel: (08) 9471 6661
                                            <br>Fax: (08) 9471 6663
                                            <br>Email: accounts@mpawa.asn.au
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="padding:10px;font-size:24px;font-weight:bold;text-align:center;">TAX INVOICE</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table border="0" style="padding:0;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding:0 0 5px 0;">Invoice to:</td>
                                        <td style="padding:0 0 5px 0;">&nbsp;</td>
                                        <td style="padding:0 0 5px 0;">Ship to:</td>
                                    </tr>
                                    <tr>
                                        <td style="width:500px;padding:10px;border:1px solid;border-color:#012391;">'
                                                .$customer_name
                                                ."<br>".$customer_address_1
                                                .'<br>'.$customer_address_2
                                                .'<br>ABN: '.$customer_dets->business_abn.'
                                        </td>
                                        <td style="width:32px;padding:10px;border:0px solid;">&nbsp;</td>
                                        <td style="width:500px;padding:10px;border:1px solid;border-color:#012391;">'
                                                .$st_customer_name
                                                ."<br>".$st_customer_address_1
                                                .'<br>'.$st_customer_address_2
                                                .'<br>&nbsp;
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table border="0" style="margin-top:15px;padding:0;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="width:160px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Date</td>
                                        <td style="width:160px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Invoice No.</td>
                                        <td style="width:200px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Your Reference</td>
                                        <td style="width:230px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Terms</td>
                                        <td style="width:148px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Due Date</td>
                                        <td style="width:120px;margin:15px 0 0 0;text-align:center;font-weight:bold;">Page</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:center;">'.$invoice_dets->invoice_date.'</td>
                                        <td style="text-align:center;">'.$invoice_num.'</td>
                                        <td style="text-align:center;">'.$client_reference.'</td>
                                        <td style="text-align:center;">'.$payment_terms.'</td>
                                        <td style="text-align:center;">'.$payment_date_display.'</td>
                                        <td style="text-align:center;">{PAGENO} of {nb}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
            </htmlpageheader>

            <htmlpagefooter name="letterfooter2">
                <div style="border-top: 1px solid #012391;font-size:12px;text-align:center; padding-top:3px; font-family:Arial; ">
                    Invoice # Page {PAGENO} of {nbpg}
                </div>
            </htmlpagefooter>
            ';

            $details = '';

            $colorRow = 1;

            for($i=0; $i<sizeof($items);$i++) {

                if($colorRow % 2 == 1) {
                    $bg = '#FFF';
                } else {
                    $bg = '#EDEDED';
                }

                $item_id = $items[$i]->product_id;
                $item_quantity = $items[$i]->quantity;
                $item_desc = $items[$i]->product_desc;
                $item_extension = $items[$i]->sub_total;

                $details .= '<tr style="background-color:'.$bg.'">
                    <td style = "width:100px;padding:5px 5px 0 8px;border-left:1px solid;border-color:#012391;text-align:center;" >'.$item_quantity.'</td >
                    <td style = "width:140px;padding:5px 8px 5px 5px;border-right:1px solid;border-left:1px solid;border-color:#012391;text-align:center;" >'.$item_id.'</td >
                    <td style = "width:640px;padding:5px 8px 5px 5px;border-right:1px solid;border-left:1px solid;border-color:#012391;text-align:left;" >'.$item_desc.'</td >
                    <td style = "width:120px;padding:5px 8px 5px 5px;border-right:1px solid;border-left:1px solid;border-color:#012391;text-align:right;" >$'.number_format($item_extension,2).'</td >
                </tr>';

                $colorRow++;
            }

            $body = '
            <div style="border:0px solid blue;width:900px;">
            <table border="1" style="border-collapse:collapse;margin:0 0 0 0;padding:0;" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <td style="width:120px;padding:5px 8px 5px 5px;border:1px solid;background-color:#012391;color:#FFF;border-color:#012391;text-align:center;">QUANTITY</td>
                        <td style="width:140px;padding:5px 8px 5px 5px;border:1px solid;background-color:#012391;color:#FFF;border-color:#012391;text-align:center;">ITEM CODE</td>
                        <td style="width:620px;padding:5px 8px 5px 5px;border:1px solid;background-color:#012391;color:#FFF;border-color:#012391;text-align:center;">DESCRIPTION</td>
                        <td style="width:120px;padding:5px 8px 5px 5px;border:1px solid;background-color:#012391;color:#FFF;border-color:#012391;text-align:center;">TOTAL</td>
                    </tr>
                </thead>
                '.$details.'
            </table>
            <table border="0" style="margin-left:0px;padding:0;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width:700px;padding:15px 10px 0 0;border-top:1px solid;vertical-align:top;color:#000;border-color:#0123911font-weight:normal;">&nbsp;</td>
                    <td style="padding:0 0 0 0;vertical-align:top;">
                        <table border="0" style="border-collapse:collapse;padding:0;color:#000;" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width:200px;padding:5px 8px 5px 5px;border-top:1px solid;border-right:1px solid;border-left:1px solid;border-color:#012391;">Sub-Total</td>
                                <td style="width:120px;padding:5px 8px 5px 5px;border-top:1px solid;border-right:1px solid;border-left:1px solid;text-align:right;border-color:#012391;">$'.number_format($invoice_dets->total,2).'</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 5px 5px 5px;border-top:0px solid;border-right:1px solid;border-left:1px solid;">GST</td>
                                <td style="padding:5px 8px 0 5px;border-top:0px solid;border-right:1px solid;border-left:1px solid;border-color:#012391;text-align:right;">$'.number_format($invoice_dets->gst,2).'</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 0 5px 5px;border-top:1px solid;border-right:1px solid;border-bottom:1px solid;border-left:1px solid;background-color:#012391;color:#FFF;border-color:#012391;">TOTAL PAYABLE</td>
                                <td style="padding:5px 8px 0 0;border-top:1px solid;border-right:1px solid;border-bottom:1px solid;border-left:1px solid;border-color:#012391;text-align:right;">$'.number_format($invoice_dets->grand_total,2).'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            </div>

            </body>
            </html>
        ';

        $footer = '
            <table width="900" border="0" style="table-layout:fixed;margin-top:5px;" cellpadding="5">
            <tr>
                <td colspan="2" style="padding:5px 0 0 0;font-size:20px;font-weight:bold;border-top:1px dashed;">How to Pay</td>
            </tr>
            <tr>
                <td style="width:480px;border:0px solid blue;vertical-align:top;">
                    <table border="0" style="margin:0 0 0 0;border:0px solid;" cellpadding="5">
                        <tr>

                            <td style="width:480px;padding:0 0 0 0;vertical-align:top;">
                                <table style="margin:0 0 0 0;border:0px solid;" cellpadding="5">
                                    <tr>
                                        <td colspan="4" style="padding:0 0 5px 0;font-weight:bold;vertical-align:top;font-size:18px">By Direct Deposit</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 0 5px;width:50px;font-size:16px;">Bank:</td>
                                        <td style="padding:0 0 0 0;width:80px;font-size:16px;">CBA</td>
                                        <td style="padding:0 0 0 0;width:60px;font-size:16px;">&nbsp;</td>
                                        <td style="padding:0 0 0 0;width:140px;font-size:16px;">&nbsp;</td>
                                    </tr>
                                    <tr style="font-size:10px;">
                                        <td style="padding:0 0 0 5px;font-size:16px;">BSB:</td>
                                        <td style="padding:0 0 0 0;font-size:16px;">066 114</td>
                                        <td style="padding:0 0 0 0;font-size:16px;">Acct No.:</td>
                                        <td style="padding:0 0 0 0;font-size:16px;">10124893</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="font-size:16px;">Please include invoice number in EFT details. A confirmation email or fax would be appreciated.</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="padding:10px 0 0 0;font-weight:bold;font-size:18px">By Credit Card via Mail/Phone (*Surcharges apply)</td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" style="padding:5px 0 0 0;font-weight:normal;font-size:16px">*Visa 2%&nbsp;&nbsp;MasterCard 2% &nbsp;&nbsp;Amex 3%&nbsp;&nbsp;Diners 3%</td>
                                    </tr>
                                    <tr>
                                         <td colspan="4" style="font-size:16px;">Telephone (08) 94716670</td>
                                    </tr>
                                    <tr>
                                         <td colspan="4" style="font-size:16px;">Card No. __ __ __ __ / __ __ __ __ / __ __ __ __ / __ __ __ __</td>
                                    </tr>
                                    <tr>
                                         <td colspan="4" style="font-size:16px;">Card Expiry Date __ __ / __ __ </td>
                                    </tr>
                                    <tr>
                                         <td colspan="4" style="font-size:16px;">Name on Card _____________________________________________ </td>
                                    </tr>
                                    <tr>
                                         <td colspan="4" style="font-size:16px;">Amount $______________ </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:350px;verical-align:top;border:0px solid #FFF;">
                    <table border="0" style="margin:5px 0 0 0;border-collapse:collapse;border:0px solid;" cellpadding="5">
                        <tr>
                            <td style="width:65px;padding:0 0 5px 0;vertical-align:top;"><img src="images/by_mail_icon.jpg" /></td>
                            <td style="padding:0 0 5px 0;vertical-align:top;">
                                <table style="margin:0 0 0 15px;border-collapse:collapse;border:0px solid;" cellpadding="5">
                                    <tr>
                                        <td style="padding:0 0 0 0;font-weight:bold;font-size:18px">By Mail</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px 0 5px 0;font-size:16px">Detach this section and mail your cheque to...</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 0 20px;font-size:16px">Master Plumbers & Gasfitters Association</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 0 20px;font-size:16px">PO Box 5218</td>
                                    </tr>
                                    <tr style="font-size:10px;">
                                        <td style="padding:0 0 30px 20px;font-size:16px">East Victoria Park, WA, 6981</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 0 5px 0;vertical-align:top;"><img src="images/by_internet_icon.jpg" /></td>
                            <td style="padding:0 0 5px 0;vertical-align:top;">
                                <table class="items" style="margin:0 0 0 15px;border-collapse:collapse;border:0px solid;" cellpadding="5">
                                    <tr>
                                        <td style="padding:0 0 0 0;font-weight:bold;font-size:18px">By Internet</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px 0 5px 20px;font-size:16px">Visit: www.masterpumbers.asn.au</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 5px 20px;font-size:16px">Click: Pay My Invoice</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 5px 20px;font-size:16px">Please include invoice number as the Reference when making payment</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding:0 0 0 0;vertical-align:top;border-top:1px solid;">
                                <table style="margin:0 0 0 0;border-collapse:collapse;border:0px solid;" cellpadding="5">
                                    <tr>
                                        <td style="width:90px;padding:0 0 5px 0;vertical-align:top;font-size:16px">Invoice #:</td>
                                        <td style="width:90px;padding:0 0 5px 0;vertical-align:top;font-weight:bold;font-size:16px">'.$invoice_num.'</td>
                                        <td style="width:130px;padding:0 0 5px 0;vertical-align:top;font-size:16px">Amount Due:</td>
                                        <td style="width:80px;padding:0 0 5px 0;vertical-align:top;font-weight:bold;font-size:16px">$'.number_format($invoice_dets->grand_total,2).'</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </table>
            ';

        $object = new \stdClass();
        $object->header = $header;
        $object->body = $body;
        $object->footer = $footer;

        return $object;

    }

    public function invoice()
    {
        $pdf_array = array();

        $header = '<!--mpdf

            <htmlpageheader name="letterheader">
                <table width="100%" style=" font-family: sans-serif;"><tr>
                    <td width="50%" style="color:#0000BB; "><span style="font-weight: bold; font-size: 14pt;">Acme Trading Co.</span><br />123 Anystreet<br />Your City<br />GD12 4LP<br /><span style="font-size: 15pt;">☎</span> 01777 123 567</td>
                    <td width="50%" style="text-align: right; vertical-align: top;">Invoice No.<br /><span style="font-weight: bold; font-size: 12pt;">0012345</span></td>
                </tr></table>
                <div style="margin-top: 1cm; text-align: right; font-family: sans-serif;">{DATE jS F Y}</div>
            </htmlpageheader>

            <htmlpagefooter name="letterfooter2">
                <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; font-family: sans-serif; ">
                    Page {PAGENO} of {nbpg}
                </div>
            </htmlpagefooter>
            mpdf-->

            <style>
                @page {
                    margin-top: 2.5cm;
                    margin-bottom: 2.5cm;
                    margin-left: 2cm;
                    margin-right: 2cm;
                    footer: html_letterfooter2;
                    background-color: pink;
                }

                @page :first {
                    margin-top: 8cm;
                    margin-bottom: 4cm;
                    header: html_letterheader;
                    /*footer: _blank;*/
                    footer: html_letterfooter2;
                    resetpagenum: 1;
                    background-color: yellow;
                }

                @page letterhead :first {
                    margin-top: 8cm;
                    margin-bottom: 4cm;
                    header: html_letterheader;
                    /*footer: _blank;*/
                    footer: html_letterfooter2;
                    resetpagenum: 1;
                    background-color: lightblue;
                }
                .letter {
                    page-break-before: always;
                    page: letterhead;
                }
            </style>';

                    $firstletter = '<div>Dear Sir or Madam,<br />
            Contents of your letter...
            <pagebreak />
            ... more letter on page 2 ...
            <pagebreak />
            ... more letter on page 3 ...
            </div>';

                    $letter = '<div class="letter">Dear Sir or Madam,<br />
            Contents of your letter...
            <pagebreak />
            ... more letter on page 2 ...->
            <pagebreak />
            ... more letter on page 3 ...
            </div>';

        $pdf_array = array([
            'header' => $header,
            'letter' => $letter,
            'firstletter' => $firstletter
        ]);

        //return $pdf_array;

        $object = new \stdClass();
        $object->header = $header;
        $object->firstletter = $firstletter;

        return $object;


    }
}