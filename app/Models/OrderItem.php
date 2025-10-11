<?php

namespace App\Models;

use App\Traits\HasActions;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasActions;
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'combo_items',
        'price',
    ];

    // JSON cast
    protected $casts = [
        'combo_items' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // combo_items ni product bilan bog‘lab chiqarish
    public function getComboItemsDetailedAttribute(): array
    {
        if (empty($this->combo_items) || !is_array($this->combo_items)) {
            return [];
        }

        return collect($this->combo_items)
            ->map(function ($item) {
                $productId = is_array($item) ? $item['id'] ?? null : $item; // integer bo‘lsa shunday olamiz
                if (!$productId) return null;

                $product = Product::find($productId);
                if (!$product) return null;

                return [
                    'id'         => $product->id,
                    'name_uz'    => $product->name_uz,
                    'name_ru'    => $product->name_ru,
                    'name_en'    => $product->name_en,
                    'image_path' => $product->generateImages(),
                    'price'      => $product->price,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
