<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\Inventory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_name',
        'product_description',
        'isRemoved',
        'price',
        'quantity_in_stock',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

}
