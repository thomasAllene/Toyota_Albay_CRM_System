<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationBacklogs extends Model
{
    use HasFactory;
    protected $table = 'application_backlogs';

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'status_id',
        'bank_id',
        'terms',
        'percentage',
        'transaction',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id')->with('team');
    }

    public function updatedBy(){
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

    public function bank(){
        return $this->belongsTo(Banks::class, 'bank_id', 'id');
    }

    public function transactions(){
        return $this->hasMany(TransactionBacklogs::class, 'application_id', 'id');
        // return $this->hasMany(Transactions::class, 'application_id');
    }


    
}
