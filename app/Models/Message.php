<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'messageable_id',
        'messageable_type',
        'phone',
        'text',
        'date',
        'status'
    ];

    public function messageable()
    {
        return $this->morphTo();
    }
}
