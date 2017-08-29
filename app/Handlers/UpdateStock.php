<?php

namespace App\Handlers;

use App\Handlers\Contracts\HandlerInterface;

class UpdateStock implements HandlerInterface
{
    public function handle($event)
    {
        //var_dump("Update Stock");

        foreach($event->basket->all() as $product){

            $product->decrement('stock',$product->quantity);
        }

    }
}
