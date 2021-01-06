<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{

    public $timestamps = false;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Permission::class);
    }

    public function childs()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions')->using(UserPermission::class);
    }

}
