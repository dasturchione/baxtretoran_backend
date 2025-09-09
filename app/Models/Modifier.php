<?php

namespace App\Models;

use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    use ModelHelperTrait;
    protected $fillable = ['name_uz', 'name_ru', 'name_en', 'image_path', 'is_active'];

    public static $helpers = [
        'folderName' => 'Modifier',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'modifier'  => [150, 150, 100],
                    'original' => [null, null]
                ];
        }

        return [];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier_items');
    }
}
