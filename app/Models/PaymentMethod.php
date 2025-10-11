<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasActions, ModelHelperTrait;

    protected $fillable = [
        'name',
        'image_path',
        'merchant_id',
        'secret_key',
    ];

    protected $hidden = [
        'secret_key',
    ];

    public static $helpers = [
        'folderName' => 'Payment',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'payment'  => [600, null, 100],
                ];
        }

        return [];
    }
}
