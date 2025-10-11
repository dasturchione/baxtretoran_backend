<?php

namespace App\Models;

use App\Traits\HasActions;
use Illuminate\Database\Eloquent\Model;

class ProductComboItem extends Model
{
    use HasActions;
    
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
