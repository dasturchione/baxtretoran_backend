<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;

class Product extends Model
{
    use HasActions, ModelHelperTrait, HasSlug;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'keywords_uz',
        'keywords_ru',
        'keywords_en',
        'ingredient_uz',
        'ingredient_ru',
        'ingredient_en',
        'price',
        'image_path',
        'category_id',
        'slug',
        'type',
        'ikpu_code',
        'package_code',
        'vat_percent',
        'is_active'

    ];

    protected $searchable = [
        'name_uz',
        'name_ru',
        'name_en',
        'price',
        'ikpu_code',
        'package_code',
        'categories.name_uz',
        'categories.name_ru',
        'categories.name_en',
    ];

    public static $helpers = [
        'folderName' => 'Product',
    ];


    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'thumb'    => [150, 150],
                    'product'  => [600, null, 90],
                    'original' => [null, null]
                ];
        }

        return [];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comboItems()
    {
        return $this->hasMany(ProductComboItem::class, 'combo_id');
    }

    public function comments()
    {
        return $this->hasMany(ProductComment::class);
    }


    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'product_modifier_items');
    }

    public function recommendations()
    {
        return $this->belongsToMany(
            Product::class,
            'product_recommendations',
            'product_id',
            'recommended_product_id'
        );
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                // original nomni olib, . ni olib tashlaymiz va keyin slug qilamiz
                return preg_replace('/[^A-Za-z0-9\s]/u', '', $this->name_ru);
            })
            ->saveSlugsTo('slug')
            ->usingLanguage('ru')
        ;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if (!empty($model->image) && Storage::disk('public')->exists($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
        });
    }
}
