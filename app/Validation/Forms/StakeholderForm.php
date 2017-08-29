<?php

namespace App\Validation\Forms;

use Respect\Validation\Validator as v;

class StakeholderForm
{
    public static function rules()
    {
        return [
            'business_name' => v::notEmpty()->setName('Business Name'),
            'company_name' => v::notEmpty()->setName('Company Name'),
            'business_abn' => v::CompanyNumberSet($_SESSION['member_acn_number'], $_SESSION['member_arbn_number']),
            'checkboxes_licensed_plumber_num' =>v::CheckLicenceType($_SESSION['set_checkboxes_licensed_plumber_num'])->setName('Plumber Licence'),
            'business_type' =>v::notEmpty()->NotSelected()->setName('Business Type'),
            'member_type' =>v::notEmpty()->NotSelected()->setName('Member Type'),
            'member_status' =>v::notEmpty()->NotSelected()->setName('Member Status'),
            'activity_type' =>v::notEmpty()->NotSelected()->setName('Activity Type'),
            'business_phone' => v::notEmpty()->setName('Business Phone'),
            'business_email' => v::notEmpty()->setName('Business Email'),
            'business_address' => v::notEmpty()->setName('Business Address'),
            'business_suburb' => v::notEmpty()->setName('Business Suburb'),
            'business_state' => v::notEmpty()->setName('Business State'),
            'business_postcode' => v::notEmpty()->setName('Business Post Code'),
            'mailing_address' => v::CheckMailingAddress($_SESSION['check_member_mailing'])->setName('Mailing Street Address'),
            'mailing_suburb' => v::CheckMailingAddress($_SESSION['check_member_mailing'])->setName('Mailing Suburb'),
            'mailing_state' => v::CheckMailingAddress($_SESSION['check_member_mailing'])->setName('Mailing State'),
            'mailing_postcode' => v::CheckMailingAddress($_SESSION['check_member_mailing'])->setName('Mailing Post Code'),
        ];

    }
}