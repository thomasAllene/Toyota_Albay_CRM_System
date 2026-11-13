<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inquiry';

    protected $fillable = [
        'inquiry_type_id',
        'customer_id',
        'vehicle_id',
        'transaction',
        'category',
        'quantity',
        'remarks',
        'date',
        'status_id',
        'notif_status',
        'is_dispute',
        'created_by',
        'status_updated_by',
        'status_updated_at',
        'updated_by',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function vehicle(){
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function inquiryType(){
        return $this->belongsTo(InquryType::class, 'inquiry_type_id', 'id');
    }

    public function updateBy(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }


}
