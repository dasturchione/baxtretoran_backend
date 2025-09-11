<?php

namespace App\Http\Controllers\Client;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function getUserInfo()
    {
        $user = Auth::user()->load('subscription');

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function updateUserInfo(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validatsiya
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'birthday' => 'sometimes|date',
        ]);

        // Ma'lumotlarni yangilash
        $user->update($validated);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'birthday' => $user->birthday ? format_date($user->birthday) : null,
            'push_notification' => $user->subscription ? (bool)$user->subscription->is_active : false,
        ]);
    }
}
