<?php

namespace App\Http\Controllers\Client;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. Telefon raqamga kod yuborish
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $code = rand(1000, 9999);

        // kodni vaqtincha cache yoki redisga yozamiz
        Cache::put("verify_{$request->phone}", $code, now()->addMinutes(5));

        // Foydalanuvchi mavjudligini tekshirish
        $alreadyRegistered = User::where('phone', $request->phone)->exists();

        // TODO: SMS servis orqali yuborish (nexmo, playmobile, etc.)
        // SmsService::send($request->phone, "Your code: {$code}");

        return response()->json([
            'message' => 'Verification code sended',
            'already_registered' => $alreadyRegistered,
            'code' => $code
        ]);
    }


    // 2. Telefon raqam + kodni tekshirish
    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required',
            'name'  => Rule::requiredIf(function () use ($request) {
                return !User::where('phone', $request->phone)->exists();
            }),
            'birthday' => Rule::requiredIf(function () use ($request) {
                return !User::where('phone', $request->phone)->exists();
            }),
        ]);

        // cache kodini tekshirish
        $cachedCode = Cache::get("verify_{$request->phone}");
        if (!$cachedCode || $cachedCode !== $request->code) {
            return response()->json(['message' => 'Kod noto‘g‘ri yoki eskirgan'], 422);
        }

        // Userni tekshirish
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            // Yangi user — create
            $user = User::create([
                'phone' => $request->phone,
                'name' => $request->name,
                'birthday' => $request->birthday,
                'phone_verified_at' => now(),
            ]);
        }

        // Tokenlar yaratish
        $accessToken = $user->createToken('access-token', ['*'], now()->addDays(1))->plainTextToken;
        $refreshToken = $user->createToken('refresh-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => now()->addDays(1)->diffForHumans(),
            'is_new_user'  => !$user->wasRecentlyCreated ? false : true, // frontend ogohlantirish uchun
        ])->cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7, // 7 kun
            '/',
            config('session.domain'),
            true,
            true,
            false,
            'Strict'
        );
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
