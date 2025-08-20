<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodeService
{
    public function getAddressFromCoords($lat, $lon, $lang = "uz_UZ")
    {
        $apiKey = config('services.yandex.api_key');

        $response = Http::get("https://geocode-maps.yandex.ru/1.x/", [
            'apikey' => $apiKey,
            'geocode' => "$lon,$lat",
            'format' => 'json',
            'lang' => $lang,
        ]);

        if ($response->successful()) {
            Log::error($response);
            return $response->json() ?? null;
        }
Log::error($response);
        return null;
    }
}
