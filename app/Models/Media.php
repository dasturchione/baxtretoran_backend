<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'file_name',
        'original_name',
        'mime_type',
        'size',
        'url',
        'disk',
    ];

    // 🔹 Dynamic accessor: full url
    public function getFullUrlAttribute(): string
    {
        return asset('storage/'.$this->file_name);
    }
}
