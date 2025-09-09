<?php

namespace App\Models;

use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use ModelHelperTrait;

    public static $helpers = [
        'folderName' => 'Banner',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'banner'  => [1920, null, 100],
                    'original' => [null, null]
                ];
        }

        return [];
    }
}
