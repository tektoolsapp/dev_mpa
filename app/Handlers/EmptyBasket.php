<?php

namespace App\Handlers;

use App\Handlers\Contracts\HandlerInterface;

class EmptyBasket implements HandlerInterface
{
    public function handle($event)
    {
        //var_dump("Empty Basket");
        $event->basket->clear();
    }
}
