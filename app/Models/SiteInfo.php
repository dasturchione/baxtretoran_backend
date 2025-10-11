<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model
{
    use HasActions, ModelHelperTrait;

    protected $fillable = [
        'logo',
        'phone',
        'email',
        'address',
        'facebook',
        'instagram',
        'telegram',
        'youtube',
        'work_time_start',
        'work_time_end',
    ];

    public static $helpers = [
        'folderName' => 'Siteinfo',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'logo':
                return [
                    'logo'  => [300, null, 90],
                    'original' => [null, null]
                ];
        }

        return [];
    }
}
