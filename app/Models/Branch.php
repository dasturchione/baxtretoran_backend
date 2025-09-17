<?php

namespace App\Models;

use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use ModelHelperTrait;
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
