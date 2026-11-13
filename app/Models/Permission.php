<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'permission_name',
        'permission_description',
    ];

    public function usertypes()
    {
        return $this->belongsToMany(Usertype::class, 'permission_usertype', 'permission_id', 'usertype_id');
    }
}
