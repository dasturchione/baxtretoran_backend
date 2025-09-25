<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name_uz'           => $this->name_uz,
            'name_ru'           => $this->name_ru,
            'name_en'           => $this->name_en,
            'description_uz'    => $this->description_uz,
            'description_ru'    => $this->description_ru,
            'description_en'    => $this->description_en,
            'slug'              => $this->slug,
            'images'            => $this->generateImages(),
            'puck_data'         => [
                'config'    => $this->config,
                'content'   => $this->content,
            ],
            'created_at'        => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'        => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
