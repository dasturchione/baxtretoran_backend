<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    public static function phone(string $phone): string
    {
        // faqat raqamlarni qoldiramiz
        $digits = preg_replace('/\D+/', '', $phone);

        // agar boshida 998 bo‘lmasa, avtomatik qo‘shamiz
        if (strlen($digits) === 9) {
            // masalan 933211377 -> 998933211377
            $digits = '998' . $digits;
        }

        return $digits;
    }

    // 10000 -> 10 000 so'm
    public static function price($amount, $currency = 'so\'m')
    {
        return number_format($amount, 0, '.', ' ') . ' ' . $currency;
    }

    // * 2025-08-13 -> 13.08.2025
    public static function date($date, $format = 'd.m.Y')
    {
        return Carbon::parse($date)->format($format);
    }
}
