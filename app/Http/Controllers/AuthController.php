<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendSmsJob;
use App\Services\PlayMobileService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    protected $smsService;

    public function __construct(PlayMobileService $smsService)
    {
        $this->smsService = $smsService;
    }
    // 1. Telefon raqamga kod yuborish
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $code = rand(1000, 9999);

        // kodni vaqtincha saqlash
        Cache::put("verify_{$request->phone}", $code, now()->addMinutes(5));

        $user = User::where('phone', $request->phone)->first();
        $alreadyRegistered = (bool) $user;

        $data = (object)[
            'phone' => phone_format($request->phone),
            'text'  => (object)[
                'code' => $code
            ]
        ];

        // auth_code template ishlatiladi
        $this->smsService->handle("auth_code", $data, $user);


        return response()->json([
            'message' => 'Verification code sent',
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
            'is_new_user'  => !$user->wasRecentlyCreated ? false : true,
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

    // Admin



    public function employeeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $employee = \App\Models\Employee::where('email', $credentials['email'])->first();

        if (! $employee || ! Hash::check($credentials['password'], $employee->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $employee->createToken('employee-token', ['*'], now()->addDay())->plainTextToken;

        return response()->json([
            'status'   => true,
            'guard'    => 'employee',
            'employee' => $employee,
            'token'    => $token,
        ]);
    }


    public function checkToken(Request $request)
    {
        $user = Auth::user(); // <- user() qo'shildi

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalid'
            ], 401);
        }

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => 'success',
            'user' => $user,

        ]);
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'employee' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }
}
