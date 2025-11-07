<?php

namespace App\Traits;

use App\User\PermissionModel as Permission;
use App\User\RoleModel as Role;

trait HasPermissionsTrait {

   public function givePermissionsTo(... $permissions) {

    $permissions = $this->getAllPermissions($permissions);
    if($permissions === null):
      return $this;
    endif;
    $this->permissions()->saveMany($permissions);
    return $this;
  }

  public function withdrawPermissionsTo( ... $permissions ) {

    $permissions = $this->getAllPermissions($permissions);
    $this->permissions()->detach($permissions);
    return $this;

  }

  public function refreshPermissions( ... $permissions ) {

    $this->permissions()->detach();
    return $this->givePermissionsTo($permissions);
  }

  public function hasPermissionTo($permission) {

    return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
  }

  public function hasPermissionThroughRole($permission) {

    foreach ($permission->roles as $role):
      if($this->roles->contains($role)):
        return true;
      endif;
    endforeach;
    return false;
  }

  public function hasRole( ... $roles ) {

    foreach ($roles as $role):
      if ($this->roles->contains('slug', $role)):
        return true;
      endif;
    endforeach;
    return false;
  }

  public function roles() {

    return $this->belongsToMany(Role::class,'users_roles', 'user_id', 'role_id');

  }
  public function permissions() {

    return $this->belongsToMany(Permission::class,'users_permissions', 'user_id', 'permission_id');

  }
  protected function hasPermission($permission) {

    return (bool) $this->permissions->where('slug', $permission->slug)->count();
  }

  protected function getAllPermissions(array $permissions) {

    return PermissionModel::whereIn('slug',$permissions)->get();
    
  }

}
