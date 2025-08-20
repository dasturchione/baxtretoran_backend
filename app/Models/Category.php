<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'is_active',
        'sort'
    ];

    protected $casts = [
        'is_active' => 'bool'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
