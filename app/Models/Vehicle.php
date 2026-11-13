<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Vehicle extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'vehicle';

    protected $fillable = [
        'unit',
        'variant',
        'color',
        'category',
        'created_by',
        'updated_by',
    ];

    public function inventory(){
        return $this->hasMany(Inventory::class, 'vehicle_id', 'id');
    }
}
