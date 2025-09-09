<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModifierResource extends JsonResource
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
            'name_uz'   => $this->name_uz,
            'name_ru'   => $this->name_ru,
            'name_en'   => $this->name_en,
            'images'    => $this->generateImages(),
            'products'  => $this->products_count,
        ];
    }
}
