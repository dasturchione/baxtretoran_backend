<?php

namespace App\Models;

use App\Traits\HasActions;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasActions;
    
    protected $fillable = ['key', 'name', 'content'];
}
