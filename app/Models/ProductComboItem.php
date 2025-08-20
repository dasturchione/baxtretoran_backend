<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComboItem extends Model
{
    protected $fillable = ['combo_id', 'product_id', 'extra_price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function combo()
    {
        return $this->belongsTo(Product::class, 'combo_id');
    }
}
