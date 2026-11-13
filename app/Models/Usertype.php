<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\Permission;

class Usertype extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    
    protected $table = 'usertypes';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($permission)
    {
        return $this->permissions->contains('permission_name', $permission);
    }

}
