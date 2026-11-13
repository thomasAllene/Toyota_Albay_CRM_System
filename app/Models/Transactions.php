<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'folder_number',
        'inquiry_id',
        'application_id',
        'reservation_id',
        'reservation_transaction_status',
        'reservation_status',
        'inventory_id',
        'team_id',
        'application_transaction_date',
        'transaction_updated_date',
        'reservation_date',
        'released_date',
        'released_remarks',
        'profit',
        'other_profit',
        'insurance',
        'status',
        'lto_remarks',
        'notif_status',
    ];

    public function inquiry(){
        return $this->belongsTo(Inquiry::class, 'inquiry_id', 'id')->with(['inquiryType', 'customer']);
    }

    public function application(){
        return $this->belongsTo(Application::class, 'application_id', 'id')->with(['vehicle', 'updatedBy']);
    }

    public function inventory(){
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


}
