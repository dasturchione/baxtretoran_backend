<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'long',
        'lat',
        'is_active',
        'user_id'
    ];

    protected $casts = [
        'lat'  => 'float',
        'long' => 'float',
        'is_active' => 'bool'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }
}
