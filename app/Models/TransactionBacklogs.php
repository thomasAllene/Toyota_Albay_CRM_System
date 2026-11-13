<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TransactionBacklogs extends Model
{
    use HasFactory;

    protected $table = 'transaction_backlogs';

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
        'status',
        'lto_remarks',
        'notif_status',
    ];

    public function inquiry(){
        return $this->belongsTo(InquiryBacklogs::class, 'inquiry_id', 'id')->with(['inquiryType', 'customer']);
    }

    public function application(){
        return $this->belongsTo(ApplicationBacklogs::class, 'application_id', 'id')->with(['vehicle', 'updatedBy']);
    }

    public function inventory(){
        return $this->belongsTo(InventoryBacklog::class, 'inventory_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


}
