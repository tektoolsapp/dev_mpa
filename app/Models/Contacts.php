<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Contacts extends Model
{
    protected $table = 'contacts';
    protected $fillable = [
        'guid',
        'type',
        'role',
        'members_id',
        'firstname',
        'surname',
        'fullname',
        'phone',
        'mobile',
        'fax',
        'email',
        'journal',
        'status',
        'row_version'
    ];

    public function contactType($type)
    {
        $contact_type = DB::table('contact_types')->where('type', $type)->get();
        return $contact_type;
    }
}