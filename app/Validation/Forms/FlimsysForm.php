<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class FlimsysForm
{
    public static function rules()
    {
        return [
            'business_name' => v::notEmpty()->setName('Trading Name'),
            'business_address' => v::notEmpty()->setName('Street Address'),
            'business_suburb' => v::notEmpty()->setName('Suburb'),
            'business_postcode' => v::notEmpty()->setName('Post Code'),
            'business_state' => v::notEmpty()->setName('State'),
            'order_method' => v::notEmpty()->NotSelected()->setName('Order Method'),
            'order_firstname' => v::notEmpty()->setName('First Name'),
            'order_surname' => v::notEmpty()->setName('Surname'),
            'order_phone' => v::notEmpty()->setName('Phone'),
            'order_email' => v::notEmpty()->setName('Email'),
            'accounts_email' => v::notEmpty()->setName('Accounts Email'),
            'order_type' => v::FlimsyTypeSet($_SESSION['order_flimsy'], $_SESSION['order_sewer_junction'], $_SESSION['order_water_main']),
            'lot_num' => v::notEmpty()->setName('Lot Number'),
            'house_num' => v::notEmpty()->setName('House Number'),
            'street_name' => v::notEmpty()->setName('Street Name'),
            'suburb' => v::notEmpty()->setName('Suburb'),
            'postcode' => v::notEmpty()->setName('Post Code'),
            'closest_cross_street' => v::CrossStreetSet($_SESSION['order_sewer_junction'], $_SESSION['order_water_main'])->setName('Cross Street'),
            'send_by' => v::notEmpty()->NotSelected()->setName('Send By Method'),
        ];
    }
}