<?php

namespace App\User;

use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{

    protected $table = 'permissions';

    public function roles() {

       return $this->belongsToMany(App\User\RoleModel::class,'roles_permissions');

    }

    public function users() {

       return $this->belongsToMany(App\User\UserModel::class,'users_permissions', 'user_id');

    }

}
