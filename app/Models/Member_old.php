<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Contacts;

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

    public function getMembers()
    {

        //$members = member::all();

        //$members = member::where('type', '<>', 'A')->get();

        //return $members;

        /*
        $primary = $this->db->table('contacts')->where([
            ['entity_id', '=', 1],
            ['role', 'V']
        ])->get()->first();
        */

        //$query = Contacts::raw("SELECT fullname FROM contacts WHERE entity_id = 1");

        //$query = Contacts::raw("(SELECT fullname FROM contacts WHERE entity_id = 1");

        //dump($query);

        //die();

        $members = Member::leftJoin('member_types', 'members.member_type', '=', 'member_types.member_type_value')
            ->leftJoin('member_status', 'members.member_status', '=', 'member_status.member_status_code')
            ->leftJoin('contacts', 'members.primary_contact', '=', 'contacts.id')
            ->where('members.id', '>', 0)
            ->select(
                'members.id',
                'members.business_name',
                'member_types.member_type_desc',
                'member_status.member_status_description',
                'contacts.fullname'
            )
            ->orderBy('members.id', 'ASC');

        return $members;

    }

    public function getExpiredMembers()
    {

        //$members = member::all();

        $members = member::where('renewal_status', '<>', 'N')->get();

        return $members;

        /*
        $primary = $this->db->table('contacts')->where([
            ['entity_id', '=', 1],
            ['role', 'V']
        ])->get()->first();
        */

        //$query = Contacts::raw("SELECT fullname FROM contacts WHERE entity_id = 1");

        //$query = Contacts::raw("(SELECT fullname FROM contacts WHERE entity_id = 1");

        //dump($query);

        //die();


        /*
        $members = Member::leftJoin('member_types', 'members.member_type', '=', 'member_types.member_type_value')
            ->leftJoin('member_status', 'members.member_status', '=', 'member_status.member_status_code')
            ->leftJoin('contacts', 'members.primary_contact', '=', 'contacts.id')
            ->where('members.member_status', '=', 'U')
            ->select(
                'members.id',
                'members.business_name',
                'member_types.member_type_desc',
                'member_status.member_status_description',
                'contacts.fullname'
            )
            ->orderBy('members.id', 'ASC');

        return $members;

        */



    }

}