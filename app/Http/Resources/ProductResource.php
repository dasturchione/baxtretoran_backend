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
            'image_path'      => $this->image_path
                ? asset('storage/' . $this->image_path)
                : null,
            'ingredient_uz'   => $this->ingredient_uz,
            'ingredient_ru'   => $this->ingredient_ru,
            'ingredient_en'   => $this->ingredient_en,
            'slug'            => $this->slug,
            'type'            => $this->type,
            'modifiers'       => $this->modifiers,
            'category'  => new CategoryResource($this->category),

        ];
    }
}
