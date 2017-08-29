<?php

namespace App\Models;

use App\Models\UserStatus;

use Illuminate\Database\Eloquent\Model;

class MpaUser extends Model

{

    protected $table = 'mpausers';
    protected $fillable = [
        'firstname',
        'surname',
        'position',
        'email',
        'phone',
        'mobile',
        'username',
        'password',
        'access',
        'status'
    ];

    public function getUsers()
    {
        $users = $this->leftJoin('user_status', 'mpausers.status', '=', 'user_status.status_type')
            //->where('mpausers.id', '>', 0)
            ->select(
                'mpausers.id',
                'mpausers.firstname',
                'mpausers.surname',
                'mpausers.position',
                'mpausers.email',
                'mpausers.phone',
                'mpausers.mobile',
                'user_status.status_description'

            )->orderBy('mpausers.id', 'ASC')
            ->get();

        return $users;
    }

}