<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchServiceArea extends Model
{
    protected $fillable = ['branch_id', 'coordinates'];
    protected $casts = [
        'coordinates' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
