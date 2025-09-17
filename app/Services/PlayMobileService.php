<?php

namespace App\Services;

use App\Models\Message;
use App\Models\SmsTemplate;
use App\Helpers\TemplateRenderer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Jobs\SendSmsJob;

class PlayMobileService
{
    public function handle(string $eventKey, object $data, $messageable): void
    {

        $template = SmsTemplate::where('key', $eventKey)->first();

        if (!$template) {
            Log::error("SMS template not found for key: {$eventKey}");
            return;
        }

        $variables = array_merge(
            ['date' => now()->format('Y-m-d')],
            $this->objectToFlatArray($data->text)
        );

        $text = TemplateRenderer::render($template->content, $variables);

        $body = [
            "messages" => [
                [
                    "recipient"   => $data->phone,
                    "message-id"  => Str::uuid()->toString(),
                    "sms" => [
                        "originator" => config('services.playmobile.originator'),
                        "content"    => ["text" => $text]
                    ]
                ]
            ]
        ];

        // save db
        $message = Message::create([
            'messageable_id'   => $messageable?->id,
            'messageable_type' => $messageable ? get_class($messageable) : null,
            'phone'            => $data->phone,
            'text'             => $text,
            'status'           => 'pending'
        ]);

        // dispatch job
        SendSmsJob::dispatch($body, $message->id)->onQueue('sms');
    }

    function objectToFlatArray($object, $prefix = '')
    {
        $array = [];

        foreach ((array) $object as $key => $value) {
            $newKey = $prefix ? "{$prefix}_{$key}" : $key;

            if (is_object($value) || is_array($value)) {
                $array = array_merge($array, $this->objectToFlatArray($value, $newKey));
            } else {
                $array[$newKey] = $value;
            }
        }

        return $array;
    }
}
