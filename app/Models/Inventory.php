<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'id',
        'year_model',
        'vehicle_id',
        'CS_number',
        'actual_invoice_date',
        'delivery_date',
        'invoice_number',
        'age',
        'status',
        'CS_number_status',
        'incoming_status',
        'remarks',
        'created_at',
        'updated_at',
        'remarks',
        'tag',
        'team_id',
        'created_by',
        'updated_by',
        'backlogs_status'
,    ];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
    public function transaction(){
        return $this->belongsTo(Transactions::class, 'id', 'inventory_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'tag', 'id');

    }
    public function team(){
        return $this->belongsTo(Team::class, 'team_id', 'id');

    }

}
