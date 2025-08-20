<?php

namespace App\Http\Controllers\Client;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. Telefon raqamga kod yuborish
    public function sendCode(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string'
            ]);

            $code = rand(1000, 9999);

            // kodni vaqtincha cache yoki redisga yozamiz
            Cache::put("verify_{$request->phone}", $code, now()->addMinutes(5));

            // TODO: SMS servis orqali yuborish (nexmo, playmobile, etc.)
            // SmsService::send($request->phone, "Your code: {$code}");

            return response()->json([
                'message' => 'Kod yuborildi',
                'code' => $code
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // 2. Telefon raqam + kodni tekshirish
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'code'  => 'required'
            ]);

            $cachedCode = Cache::get("verify_{$request->phone}");
            if (!$cachedCode || $cachedCode !== $request->code) {
                return response()->json(['message' => 'Kod noto‘g‘ri yoki eskirgan'], 422);
            }

            // User mavjudmi? yo‘q bo‘lsa ro‘yxatdan o‘tkazamiz
            $user = User::updateOrCreate(
                ['name' => $request->name],
                ['phone' => $request->phone],
                ['phone_verified_at' => now()]
            );

            // Access va Refresh token
            $accessToken = $user->createToken('access-token', ['*'], now()->addDays(1))->plainTextToken;
            $refreshToken = $user->createToken('refresh-token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'access_token' => $accessToken,
                'token_type'   => 'Bearer',
                'expires_in'   => Carbon::now()->addDays(1)->diffForHumans()
            ])->cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 7, // 7 kun
                '/',
                config('session.domain'), // yoki null
                true,
                true,
                false,
                'Strict'
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // 3. Refresh qilish
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token topilmadi'], 401);
        }

        // Refresh tokenni tekshirish
        $tokenModel = PersonalAccessToken::findToken($refreshToken);

        if (!$tokenModel || $tokenModel->name !== 'refresh-token') {
            return response()->json(['message' => 'Notog‘ri yoki eskirgan refresh token', 'token' => $refreshToken, 'hashed' => hash('sha256', $refreshToken)], 401);
        }

        $user = $tokenModel->tokenable; // shu refresh token kimga tegishli bo‘lsa

        // Yangi access token yaratamiz
        $accessToken = $user->createToken('access', ['*'], now()->addDays(1))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => now()->addDays(1)->timestamp
        ]);
    }

    // 4. Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        cookie()->queue(cookie()->forget('refresh_token'));

        return response()->json(['message' => 'Tizimdan chiqildi']);
    }
}
