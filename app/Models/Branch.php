<?php

namespace App\Models;

use App\Traits\HasActions;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasActions, ModelHelperTrait;
    protected $fillable = [
        'name',
        'long',
        'lat',
        'work_time_start',
        'work_time_end',
        'is_active'
    ];

    protected $casts = [
        'lat'  => 'float',
        'long' => 'float',
        'is_active' => 'boolean'
    ];

    public function serviceAreas()
    {
        return $this->hasMany(BranchServiceArea::class);
    }
}
