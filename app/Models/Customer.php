<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customer';

    protected $fillable = [
        'inquiry_id',
        'inquiry_type_id',
        'customer_first_name',
        'customer_last_name',
        'department_name',
        'company_name',
        'contact_number',
        'gender',
        'birthdate',
        'age',
        'source',
        'address',
        'created_by',
        'updated_by',
    ];

    public function inquiry(){
        return $this->belongsTo(Inquiry::class, 'inquiry_id', 'id');
    }
}
