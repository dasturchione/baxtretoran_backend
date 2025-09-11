<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $body;
    protected int $messageId;

    public function __construct(array $body, int $messageId)
    {
        $this->body = $body;
        $this->messageId = $messageId;
    }

    public function handle()
    {
        Log::info("SendSmsJob ishga tushdi", [
            'body' => $this->body,
            'message_id' => $this->messageId,
        ]);

        $message = Message::find($this->messageId);

        if (! $message) {
            Log::warning("Message topilmadi", ['id' => $this->messageId]);
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . config('services.playmobile.auth')
            ])
                ->timeout(10) // agar tarmoqda muammo bo‘lsa exception tashlaydi
                ->withBody(json_encode($this->body), 'application/json')
                ->post(config('services.playmobile.endpoint'));

            if ($response->successful()) {
                $message->update(['status' => 'sent']);
            } else {
                // bu yerga API noto‘g‘ri javoblari tushadi
                Log::warning("SMS yuborishda muvaffaqiyatsiz", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                $message->update(['status' => 'failed']);
            }
        } catch (\Throwable $e) {
            // bu faqat timeout, tarmoq muammosi yoki noto‘g‘ri config bo‘lsa tushadi
            Log::error("SendSmsJob xato: " . $e->getMessage());
            $message->update(['status' => 'failed']);
        }
    }
}
