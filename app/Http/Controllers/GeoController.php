<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchServiceArea;
use App\Models\SiteInfo;
use Illuminate\Http\Request;
use App\Services\GeocodeService;

class GeoController extends Controller
{
    public function getAddress(Request $request, GeocodeService $geocodeService)
    {
        $request->validate([
            'lat'  => 'required|numeric',
            'long' => 'required|numeric',
            "lang" => 'required|in:uz_UZ,ru_RU,en_US'
        ]);

        $response = $geocodeService->getAddressFromCoords(
            $request->lat,
            $request->long,
            $request->lang
        );

        // Agar xato bo‘lsa
        if (!$response->successful()) {
            $body = $response->json();
            return response()->json([
                'error'  => $body['message'],
            ], $response->status());
        }

        // To‘g‘ri javob bo‘lsa
        return response()->json($response->json()['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']);
    }

}
