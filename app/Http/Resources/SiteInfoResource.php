<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'logo'  => $this->generateImages('logo'),
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'telegram' => $this->telegram,
            'youtube' => $this->youtube,
            'work_time_start' => $this->work_time_start,
            'work_time_end' => $this->work_time_end,
        ];
    }
}
