<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $data = PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id' => Auth::user()->id,
                'p256dh' => $request->keys['p256dh'],
                'auth' => $request->keys['auth'],
            ]
        );

        return response()->json(['message' => "Subscription created successfully", 'data' => $data]);
    }
}
