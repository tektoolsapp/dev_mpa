<?php

namespace App\Models;

use App\Models\Product;
//use App\Models\Address;
//use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
//use Carbon\Carbon;

class OrdersProductsPivot extends Model
{
    protected $table = 'orders_products';

    public function product()
    {
        return $this->hasOne('App\Models\Product', 'product_id');
    }

}