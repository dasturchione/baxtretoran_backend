<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasActions, ModelHelperTrait;

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
