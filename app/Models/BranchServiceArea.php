<?php

namespace App\Models;

use App\Traits\HasActions;
use Illuminate\Database\Eloquent\Model;

class BranchServiceArea extends Model
{
    use HasActions;
    
    protected $fillable = ['branch_id', 'coordinates'];
    protected $casts = [
        'coordinates' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
