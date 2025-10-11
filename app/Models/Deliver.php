<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliver extends Model
{
    use HasActions, HasFactory, ModelHelperTrait;

    protected $fillable = [
        'name',
        'phone',
        'telegram_id',
        'image_path',
        'status',
        'is_active'
    ];

    public static $helpers = [
        'folderName' => 'Deliver',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'thumb'    => [150, 150],
                    'profile'  => [300, null, 90],
                    'original' => [null, null]
                ];
        }

        return [];
    }

    // Agar kerak bo‘lsa order bilan bog‘lanishi
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
