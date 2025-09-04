<?php

namespace App\Models;

use App\Traits\ModelHelperTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasRoles, HasApiTokens, Notifiable, ModelHelperTrait;

    protected $guard_name = 'employee';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
    ];

    public static $helpers = [
        'folderName' => 'Employee',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
