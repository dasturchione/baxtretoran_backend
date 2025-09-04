<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\ModelHelperTrait;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasApiTokens, HasFactory, Notifiable, ModelHelperTrait;

    protected $guard_name = 'user';

    protected $fillable = [
        'name',
        'birthday',
        'phone',
        'image_path',
        'phone_verified_at',
        'password',
    ];

    public static $helpers = [
        'folderName' => 'User',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscription()
    {
        return $this->hasOne(PushSubscription::class);
    }
}
