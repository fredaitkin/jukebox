<?php

namespace App\User;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{

    protected $table = 'roles';

    public function permissions() {

       return $this->belongsToMany(App\User\PermissionModel::class,'roles_permissions');

    }

    public function users() {

       return $this->belongsToMany(App\User\UserModel::class,'users_roles', 'user_id');

    }
}
