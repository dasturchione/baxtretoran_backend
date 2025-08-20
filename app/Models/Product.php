<?php

namespace App\Models;

use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'ingredient_uz',
        'ingredient_ru',
        'ingredient_en',
        'price',
        'image_path',
        'category_id',
        'slug',
        'type'

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comboItems()
    {
        return $this->hasMany(ProductComboItem::class, 'combo_id');
    }

    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'product_modifier_items');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_ru')
            ->saveSlugsTo('slug')
            ->usingLanguage('ru');
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
