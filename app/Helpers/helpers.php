<?php

use App\Helpers\FormatHelper;
use Illuminate\Support\Facades\App;

if (!function_exists('phone_format')) {
    function phone_format($phone)
    {
        return FormatHelper::phone($phone);
    }
}

if (!function_exists('price_format')) {
    function price_format($amount, $currency = 'so\'m')
    {
        return FormatHelper::price($amount, $currency);
    }
}

if (!function_exists('date_format_short')) {
    function date_format_short($date, $format = 'd.m.Y')
    {
        return FormatHelper::date($date, $format);
    }
}

if (!function_exists('date_format_long')) {
    function date_format_long($date)
    {
        return FormatHelper::date_long($date);
    }
}

if (! function_exists('locale_route')) {
    function locale_route($name, $parameters = [], $absolute = true)
    {
        $locale = App::getLocale();

        // Agar parametr massiv bo'lsa locale qo'shamiz
        if (is_array($parameters)) {
            $parameters = array_merge(['locale' => $locale], $parameters);
        } else {
            // Agar bitta parametr bo'lsa uni massivga o‘tkazamiz
            $parameters = ['locale' => $locale, $parameters];
        }

        return route($name, $parameters, $absolute);
    }
}
