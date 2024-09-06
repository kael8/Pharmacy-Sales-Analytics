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
        'product_name',
        'product_description',
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
}
