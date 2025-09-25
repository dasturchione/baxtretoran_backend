<?php

namespace App\Models;

use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Service extends Model
{
    use HasFactory, HasSlug, ModelHelperTrait;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'description_uz',
        'description_ru',
        'description_en',
        'slug',
        'image_path',
        'config',
        'content',
    ];

    protected $casts = [
        'config' => 'array',
        'content' => 'array',
    ];

    public static $helpers = [
        'folderName' => 'Service',
    ];


    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'original' => [null, null]
                ];
        }

        return [];
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
}
