<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PAYMENT_PROCESS = 'payment_process';
    case PAYMENT_FAILED = 'payment_failed';
    case PAID = 'paid';

    case ORDERED = 'ordered';
    case CONFIRMED = 'confirmed';
    case COOKING = 'cooking';
    case READY = 'ready';

    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';

    case WAITING_PICKUP = 'waiting_pickup';
    case PICKED_UP = 'picked_up';

    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT_PROCESS => 'To‘lov jarayonida',
            self::PAYMENT_FAILED => 'To‘lov amalga oshmadi',
            self::PAID => 'To‘lov muvaffaqiyatli',
            self::ORDERED => 'Buyurtma tushdi',
            self::CONFIRMED => 'Restoran tasdiqladi',
            self::COOKING => 'Taom tayyorlanmoqda',
            self::READY => 'Tayyor bo‘ldi',
            self::DELIVERING => 'Kuryer olib ketdi',
            self::DELIVERED => 'Yetkazildi',
            self::WAITING_PICKUP => 'Tayyor, mijoz hali olmadi',
            self::PICKED_UP => 'Mijoz olib ketdi',
            self::CANCELLED => 'Bekor qilingan',
        };
    }

    public static function flow(): array
    {
        return [
            self::PAYMENT_PROCESS->value => [self::PAYMENT_FAILED, self::PAID],
            self::PAID->value            => [self::ORDERED],
            self::ORDERED->value         => [self::CONFIRMED],
            self::CONFIRMED->value       => [self::COOKING],
            self::COOKING->value         => [self::READY],
            self::READY->value           => [self::DELIVERING, self::WAITING_PICKUP],
            self::DELIVERING->value      => [self::DELIVERED],
            self::WAITING_PICKUP->value  => [self::PICKED_UP],
            self::CANCELLED->value       => [],
            self::DELIVERED->value       => [],
            self::PICKED_UP->value       => [],
        ];
    }


    public static function options(): array
    {
        return array_map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label()
        ], self::cases());
    }
}
