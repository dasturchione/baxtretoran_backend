<?php

namespace App\Http\Controllers\Client;

use App\Http\Resources\AddressResource;
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
            'name_uz' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'long'    => 'required|numeric',
            'lat'     => 'required|numeric',
            'is_active' => 'boolean'
        ]);

        $address = Auth::user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Address created successfully',
            'data' => $address
        ], 201);
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
