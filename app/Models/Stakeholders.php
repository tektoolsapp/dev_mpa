<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stakeholders extends Model
{
    protected $table = 'stakeholders';
    protected $fillable = [
        'guid',
        'customer_id',
        'myob_integ_status',
        'myob_uid',
        'myob_row',
        'business_name',
        'company_name',
        'business_abn',
        'business_acn',
        'business_arbn',
        'business_type',
        'member_type',
        'member_status',
        'activity_type',
        'business_phone',
        'business_fax',
        'business_email',
        'accounts_email',
        'business_address',
        'business_suburb',
        'business_state',
        'business_postcode',
        'set_mailing_address',
        'mailing_address',
        'mailing_suburb',
        'mailing_state',
        'mailing_postcode',
        'primary_contact',
        'row_version'
    ];

    public function contacts()
    {
        return $this->hasMany('App\Models\Contacts');
    }

    public function getMemberTradingName($id){
        $trading_name = $this->where('id', $id)->get()->first();

        return $trading_name;
    }

    public function getStakeholders($types=null)
    {
        if(!is_null($types)) {

            $types_array = explode(',', $types);

            for ($i = 0; $i < count($types_array); $i++) {
                if($types_array[$i] == 'C') {
                    $type_c = 'C';
                } elseif($types_array[$i] == 'P') {
                    $type_p = 'P';
                } elseif($types_array[$i] == 'U') {
                    $type_u = 'U';
                }
            }

        }

        $stakeholders = $this->leftJoin('stakeholder_types', 'stakeholders.member_type', '=', 'stakeholder_types.member_type_value')
            ->leftJoin('stakeholder_status', 'stakeholders.member_status', '=', 'stakeholder_status.member_status_code')
            ->leftJoin('contacts', 'stakeholders.primary_contact', '=', 'contacts.id')
            ->when($type_c, function ($q) use ($type_c) {
                return $q->orwhere('stakeholder.member_status', $type_c);
            })
            ->when($type_p, function ($q) use ($type_p) {
                return $q->orwhere('stakeholder.member_status', $type_p);
            })
            ->when($type_u, function ($q) use ($type_u) {
                return $q->orwhere('stakeholder.member_status', $type_u);
            })
            ->select(
            'stakeholders.id',
            'stakeholders.business_name',
            'stakeholder_types.member_type_desc',
            'stakeholder_status.member_status_description',
            'contacts.fullname'
        )->orderBy('stakeholders.id', 'ASC')
        ->paginate(5)->appends($_GET);

        return $stakeholders;
    }

    public function getMemberByName($name)
    {
        $member = $this->leftJoin('member_types', 'members.member_type', '=', 'member_types.member_type_value')
            ->leftJoin('member_status', 'members.member_status', '=', 'member_status.member_status_code')
            ->leftJoin('contacts', 'members.primary_contact', '=', 'contacts.id')
            ->where('members.business_name', '=', $name)
            ->select(
                'members.id',
                'members.customer_id',
                'members.business_name',
                'member_types.member_type_desc',
                'members.business_address',
                'members.business_suburb',
                'members.business_state',
                'members.business_postcode',
                'members.accounts_email',
                'members.payment_method',
                'member_status.member_status_description',
                'contacts.fullname'
            )->orderBy('members.id', 'ASC')
            ->get()->first();

        return $member;
    }
}