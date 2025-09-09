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
        'image_path',
    ];

    public static $helpers = [
        'folderName' => 'Employee',
    ];

    public function imageSize($field)
    {
        switch ($field) {
            case 'image_path':
                return [
                    'thumb'    => [150, 150],
                    'profile'  => [300, null, 90],
                    'original' => [null, null]
                ];
        }

        return [];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
