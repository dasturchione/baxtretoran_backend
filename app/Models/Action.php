<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = [
        'actor_type', 'actor_id', 'action',
        'actionable_type', 'actionable_id', 'changes',
    ];

    protected $casts = ['changes' => 'array'];

    public function actor()
    {
        return $this->morphTo(); // bu user ham bo'lishi mumkin, employee ham
    }

    public function actionable()
    {
        return $this->morphTo();
    }
}

