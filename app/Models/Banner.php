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
                    'large'  => [300, null, 90],
                    'original' => [null, null]
                ];
        }

        return [];
    }
}
