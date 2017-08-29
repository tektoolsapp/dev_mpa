<?php

namespace App\Models;

//use App\Models\Product;
//use App\Models\Address;
//use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoices extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_id',
        'myob_uid',
        'myob_row_version',
        'myob_id',
        'invoice_type',
        'invoice_ref',
        'payment_terms',
        'customer_id',
        'customer_reference',
        'invoice_description',
        'invoice_status'
    ];

    public function getInvoiceDateAttribute( $value ) {
        return Carbon::parse($value)->format('d-m-Y');
    }

}