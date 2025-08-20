<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeocodeService;

class GeoController extends Controller
{
    public function getAddress(Request $request, GeocodeService $geocodeService)
    {
        // Foydalanuvchi tilini aniqlash
        $locale = app()->getLocale(); // uz, ru, en
        // GET query parameterlarini validatsiya qilish
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        // Yandex API uchun til mapping
        $lang = match ($locale) {
            'uz' => 'uz_UZ',
            'ru' => 'ru_RU',
            default => 'en_US',
        };

        // Yandex geocode API orqali manzilni olish
        $address = $geocodeService->getAddressFromCoords(
            $request->query('lat'),
            $request->query('lon'),
            $lang
        );

        // Javobni JSON formatida qaytarish
        return response()->json([
            'address' => $address
        ]);
    }
}
