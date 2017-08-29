<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Address;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'hash',
        'total',
        'paid',
        'address_id',
        'customer_type',
        'customer_id'
    ];

    public function address()
    {
     return $this->belongsTo(Address::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_products')->withPivot('quantity');
    }

    /*
    public function payment()
    {
        $this->hasOne(Payment::class);
    }
    */

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

}