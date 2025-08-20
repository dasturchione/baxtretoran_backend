<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = ['name_uz', 'name_ru', 'name_en', 'image_path'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier_items');
    }
}
