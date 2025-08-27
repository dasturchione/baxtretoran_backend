<?php

namespace App\Jobs;

use App\Services\PlayMobileService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $message;

    public function __construct($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
    }

    public function handle(PlayMobileService $smsService)
    {
        $smsService->send($this->to, $this->message);
    }
}
