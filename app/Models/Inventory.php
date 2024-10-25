<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_date',
        'quantity',
        'price',
        'expiration_date',
        'batch_id',
        'cost_price',
        'action_type',
        'created_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Method to get batches by product ID
    public static function getBatchesByProduct($productId)
    {
        return self::select(['batch_id', 'price', 'quantity', 'expiration_date'])
            ->where('product_id', $productId)
            ->whereIn('id', function ($query) use ($productId) {
                $query->selectRaw('MAX(id)')
                    ->from('inventories')
                    ->where('product_id', $productId)
                    ->groupBy('batch_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
