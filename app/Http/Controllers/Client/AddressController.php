<?php

namespace App\Http\Controllers\Client;

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
        return response()->json($addresses);
    }

    // Create
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_uz' => 'required|string|max:255',
                'name_ru' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'long'    => 'required|numeric',
                'lat'     => 'required|numeric',
                'is_active' => 'boolean'
            ]);

            // $validated['user_id'] = Auth::id();

            // dd($validated);

            $address = Auth::user()->addresses()->create($validated);

            return response()->json([
                'message' => 'Address created successfully',
                'data' => $address
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Destroy
    public function destroy($id)
    {
        try {
            $address = Auth::user()->addresses()->findOrFail($id);
            $address->delete();

            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
