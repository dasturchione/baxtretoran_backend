<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\ModelHelperTrait;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    use ModelHelperTrait;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'payment_status',
        'deliver_id',
        'delivery_type',
        'user_address_id',
        'branch_id',
        'status',
    ];


    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliver()
    {
        return $this->belongsTo(Deliver::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->product->price);
    }
}
