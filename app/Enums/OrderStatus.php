<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PAYMENT_PROCESS = 'payment_process';   // To‘lov jarayonida
    case PAYMENT_FAILED = 'payment_failed';     // To‘lov amalga oshmadi
    case PAID = 'paid';                         // To‘lov muvaffaqiyatli

    case ORDERED = 'ordered';                   // Buyurtma tushdi
    case CONFIRMED = 'confirmed';               // Restoran tasdiqladi
    case COOKING = 'cooking';                   // Taom tayyorlanmoqda
    case READY = 'ready';                       // Tayyor bo‘ldi

    // Delivery uchun
    case DELIVERING = 'delivering';             // Kuryer olib ketdi
    case DELIVERED = 'delivered';               // Yetkazildi

    // Takeaway uchun
    case WAITING_PICKUP = 'waiting_pickup';     // Tayyor, mijoz hali olmadi
    case PICKED_UP = 'picked_up';               // Mijoz olib ketdi

    // Umumiy
    case CANCELLED = 'cancelled';               // Bekor qilingan

    /**
     * Statusga izoh qaytarish
     */
    public function label(): string
    {
        return match($this) {
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

    /**
     * Barcha statuslar va izohlar
     */
    public static function options(): array
    {
        return array_map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label()
        ], self::cases());
    }
}
