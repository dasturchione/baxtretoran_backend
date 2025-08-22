<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'payment_method_id',
        'payment_status',
        'deliver_id',
        'delivery_type',
        'user_address_id',
        'branch_id',
        'status',

    ];
}
