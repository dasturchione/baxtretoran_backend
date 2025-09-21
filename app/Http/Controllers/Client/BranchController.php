<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\BranchServiceArea;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    private $branchModel;
    private $branchServiceAreaModel;

    public function __construct(Branch $branchModel, BranchServiceArea $branchServiceAreaModel)
    {
        $this->branchModel = $branchModel;
        $this->branchServiceAreaModel = $branchServiceAreaModel;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->branchModel->active()->with('serviceAreas');

        if ($perPage) {
            $branch = $query->paginate($perPage);
        } else {
            $branch = $query->get();
        }
        return BranchResource::collection($branch);
    }

    public function nearestBranch(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $lat = $request->lat;
        $long = $request->long;

        // Eng yaqin branchni topish (masofasi bilan)
        $branch = $this->branchModel::selectRaw("
        id, name, lat, `long`,
        work_time_start, work_time_end,
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

        $serviceArea = $this->branchServiceAreaModel::where('branch_id', $branch->id)->first();

        if ($serviceArea) {
            $polygon = $serviceArea->coordinates; // bu [ [lat, long], [lat, long], ... ] formatida bo‘lishi kerak

            if (!$this->pointInPolygon([$long, $lat], $polygon)) {
                return response()->json([
                    'error' => "OUT_OF_SERVICE_AREA",
                    'message' => 'User is outside branch service area',
                    'nearest' => [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'distance_km' => round($branch->distance, 2)
                    ]
                ], 404);
            }
        }

        // Ish vaqti tekshirish
        $now = now()->format('H:i:s');
        $isAvailable = ($branch->work_time_start <= $now && $now <= $branch->work_time_end);

        if (!$isAvailable) {
            return response()->json([
                'error' => "BRANCH_CLOSED",
                'message' => 'Branch is currently closed',
                'nearest' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'distance_km' => round($branch->distance, 2)
                ]
            ], 403);
        }

        return response()->json([
            'id'          => $branch->id,
            'name'        => $branch->name,
            'lat'         => $branch->lat,
            'long'        => $branch->long,
            'distance_km' => round($branch->distance, 2),
        ]);
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
}
