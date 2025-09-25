<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'images'        => $this->generateImages(),
            'link'          => $this->link,
            'is_active'     => $this->is_active,
            'created_at'    => format_date($this->created_at)
        ];
    }
}
