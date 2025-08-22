<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'merchant_id',
        'secret_key',
    ];

    protected $hidden = [
        'secret_key',
    ];
}
