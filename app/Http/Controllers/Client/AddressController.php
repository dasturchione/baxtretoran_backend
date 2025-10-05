<?php

namespace App\Http\Controllers\Client;

use App\Http\Resources\AddressResource;
use App\Models\Branch;
use App\Models\BranchServiceArea;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    private $model;

    public function __construct(UserAddress $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        $addresses = Auth::user()->addresses; // relation orqali olish
        return AddressResource::collection($addresses);
    }

    // Create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_uz'   => 'required|string|max:255',
            'name_ru'   => 'required|string|max:255',
            'name_en'   => 'required|string|max:255',
            'long'      => 'required|numeric',
            'lat'       => 'required|numeric',
            'is_active' => 'boolean'
        ]);

        // Eng yaqin branchni topamiz
        $lat = $validated['lat'];
        $long = $validated['long'];

        $branch = Branch::selectRaw("
        id, name, lat, `long`,
        (6371 * acos(
            cos(radians(?)) * cos(radians(lat))
            * cos(radians(`long`) - radians(?))
            + sin(radians(?)) * sin(radians(lat))
        )) AS distance
    ", [$lat, $long, $lat])
            ->orderBy('distance')
            ->first();

        if (!$branch) {
            return response()->json([
                'error' => "BRANCH_NOT_FOUND",
                'message' => 'No branches found',
            ], 404);
        }

        // Branch service hududini tekshiramiz
        $serviceArea = BranchServiceArea::where('branch_id', $branch->id)->first();
        if ($serviceArea) {
            $polygon = $serviceArea->coordinates; // [ [lat, long], [lat, long], ...]

            // ⚠️ E'tibor: pointInPolygon funksiyasiga (long, lat) ko‘rinishida yuboramiz
            if (!$this->pointInPolygon([$lat, $long], $polygon)) {
                return response()->json([
                    'error' => "OUT_OF_SERVICE_AREA",
                    'message' => 'Address is outside branch service area',
                    'nearest' => [
                        'id'   => $branch->id,
                        'name' => $branch->name,
                        'distance_km' => round($branch->distance, 2)
                    ]
                ], 422); // Unprocessable Entity
            }
        }

        // Agar xizmat hududida bo‘lsa create qilamiz
        $address = Auth::user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Address created successfully',
            'data'    => $address
        ], 201);
    }

    /**
     * Point in Polygon algoritmi
     */
    private function pointInPolygon(array $point, array $polygon): bool
    {
        [$x, $y] = $point;
        $inside = false;
        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            [$xi, $yi] = $polygon[$i];
            [$xj, $yj] = $polygon[$j];
            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-10) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_uz' => 'sometimes|required|string|max:255',
            'name_ru' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'long'    => 'sometimes|required|numeric',
            'lat'     => 'sometimes|required|numeric',
            'is_active' => 'sometimes|boolean'
        ]);

        // Eng yaqin branchni topamiz
        $lat = $validated['lat'];
        $long = $validated['long'];

        $branch = Branch::selectRaw("
        id, name, lat, `long`,
        (6371 * acos(
            cos(radians(?)) * cos(radians(lat))
            * cos(radians(`long`) - radians(?))
            + sin(radians(?)) * sin(radians(lat))
        )) AS distance
    ", [$lat, $long, $lat])
            ->orderBy('distance')
            ->first();

        if (!$branch) {
            return response()->json([
                'error' => "BRANCH_NOT_FOUND",
                'message' => 'No branches found',
            ], 404);
        }

        // Branch service hududini tekshiramiz
        $serviceArea = BranchServiceArea::where('branch_id', $branch->id)->first();
        if ($serviceArea) {
            $polygon = $serviceArea->coordinates; // [ [lat, long], [lat, long], ...]

            // ⚠️ E'tibor: pointInPolygon funksiyasiga (long, lat) ko‘rinishida yuboramiz
            if (!$this->pointInPolygon([$lat, $long], $polygon)) {
                return response()->json([
                    'error' => "OUT_OF_SERVICE_AREA",
                    'message' => 'Address is outside branch service area',
                    'nearest' => [
                        'id'   => $branch->id,
                        'name' => $branch->name,
                        'distance_km' => round($branch->distance, 2)
                    ]
                ], 422); // Unprocessable Entity
            }
        }

        $address = Auth::user()->addresses()->findOrFail($id);

        $address->update($validated);

        return response()->json([
            'message' => 'Address updated successfully',
            'data' => $address
        ], 200);
    }

    // Destroy
    public function destroy($id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
