<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Member extends Model

{

    protected $table = 'members';

    protected $fillable = [

        'guid',
        'business_name',
        'company_name',
        'business_abn',
        'business_acn',
        'business_arbn',
        'business_type',
        'member_type',
        'member_status',
        'date_joined',
        'date_resigned',
        'licence_types',
        'activity_type',
        'business_phone',
        'business_fax',
        'business_address',
        'business_suburb',
        'business_state',
        'business_postcode',
        'mailing_address',
        'mailing_suburb',
        'mailing_state',
        'mailing_postcode',
        'row_version'

    ];




}