<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'bank_transaction';

    protected $fillable = [
        'application_id',
        'bank_id',
        'created_by',
        'updated_by',
        'approval_date',
        'approval_status',
        'is_preferred',
    ];

    public function bank(){
        return $this->belongsTo(Banks::class, 'bank_id', 'id');
    }

    public function application(){
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }
}
