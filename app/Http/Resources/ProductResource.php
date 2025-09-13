<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name_uz'         => $this->name_uz,
            'name_ru'         => $this->name_ru,
            'name_en'         => $this->name_en,
            'price'           => $this->price,
            'images'          => $this->generateImages(),
            'ingredient_uz'   => $this->ingredient_uz,
            'ingredient_ru'   => $this->ingredient_ru,
            'ingredient_en'   => $this->ingredient_en,
            'keywords_uz' => $this->keywords_uz,
            'keywords_ru' => $this->keywords_ru,
            'keywords_en' => $this->keywords_en,
            'slug'            => $this->slug,
            'type'            => $this->type,
            'modifiers'       => $this->modifiers,
            'category'  => new CategoryResource($this->category),

        ];
    }
}
