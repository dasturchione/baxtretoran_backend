<?php

use App\Helpers\FormatHelper;

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

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd.m.Y')
    {
        return FormatHelper::date($date, $format);
    }
}
