<?php

namespace App\Models;

use App\Traits\HasActions;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasActions;
    
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
