<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    // 998901234567 -> +998 (90) 123 45 67
    public static function phone($phone)
    {
        $raw = preg_replace('/\D/', '', $phone);

        if (preg_match('/^998(\d{2})(\d{3})(\d{2})(\d{2})$/', $raw, $matches)) {
            return "+998 ({$matches[1]}) {$matches[2]} {$matches[3]} {$matches[4]}";
        }

        return $phone;
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

    //  * 2025-08-13 -> 13 Avgust 2025
    public static function date_long($date)
    {
        return Carbon::parse($date)->translatedFormat('j F Y');
    }
}
