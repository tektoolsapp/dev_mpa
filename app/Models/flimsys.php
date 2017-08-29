<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Flimsys extends Model

{
    protected $table = 'flimsy_requests';
    protected $fillable = [
        'id',
        'guid',
        'operator',
        'customer_type',
        'customer_id',
        'ordered_by',
        'request_datetime',
        'process_datetime',
        'discount_pricing',
        'order_po',
        'order_method',
        'payment_method',
        'order_type',
        'contours',
        'lot_num',
        'house_num',
        'street_name',
        'suburb',
        'postcode',
        'closest_cross_street',
        'send_by',
        'order_total',
        'status',
        'payment_status',
        'order_id',
        'invoice_id',
        'district',
        'field_book',
        'page_num',
        'row_version',
    ];

    public function getRequestDatetimeAttribute( $value ) {
        return Carbon::parse($value)->format('d-m-Y');
    }
}