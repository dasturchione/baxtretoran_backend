<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'long',
        'lat',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
