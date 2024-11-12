<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundAmount extends Model
{
    protected $table = 'refund_amount';

    protected $fillable = [
        'inventory_id',
        'amount',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}