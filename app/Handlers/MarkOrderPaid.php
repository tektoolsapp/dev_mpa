<?php

namespace App\Handlers;

use App\Handlers\Contracts\HandlerInterface;

class MarkOrderPaid implements HandlerInterface
{
    public function handle($event)
    {
        //var_dump("Mark Order Paid");

        $event->order->update([
            'paid' => true
        ]);
    }
}
