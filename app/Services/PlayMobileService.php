<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PlayMobileService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.playmobile.url');
        $this->apiKey = config('services.playmobile.key');
    }

    /**
     * SMS yuborish
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        $response = Http::post($this->apiUrl, [
            'to'      => $to,
            'message' => $message,
            'api_key' => $this->apiKey,
        ]);

        return $response->successful();
    }
}
