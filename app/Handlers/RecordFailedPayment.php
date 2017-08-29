<?php

namespace App\Handlers;

use App\Handlers\Contracts\HandlerInterface;

class RecordFailedPayment implements HandlerInterface
{
    public function handle($event)
    {
        //var_dump("Record Failed Payment");

        $event->order->payment()->create([
            'failed' => true,
            'transaction_id' => null
        ]);
    }
}
