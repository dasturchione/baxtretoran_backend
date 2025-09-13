<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'phone'         => $this->phone,
            'telegram_id'   => $this->telegram_id,
            'images'        => $this->generateImages(),
            'status'        => $this->status,
            'is_active'     => $this->is_active
        ];
    }
}
