<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductShowResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name_uz'       => $this->name_uz,
            'name_ru'       => $this->name_ru,
            'name_en'       => $this->name_en,
            'category_id'   => $this->category_id,
            'ingredient_uz' => $this->ingredient_uz,
            'ingredient_ru' => $this->ingredient_ru,
            'ingredient_en' => $this->ingredient_en,
            'ikpu_code'     => $this->ikpu_code,
            'package_code'  => $this->package_code,
            'vat_percent'   => $this->vat_percent,
            'modifier'      => ModifierResource::collection($this->modifiers),
            'price'         => $this->price,
            'image_path'    => $this->generateImages(),
            'type'          => $this->type,
            'combo_items'   => $this->transformComboItems(),
        ];
    }

    protected function transformComboItems(): array
    {
        if (!$this->relationLoaded('comboItems')) return [];

        return $this->comboItems
            ->filter(fn($item) => $item->relationLoaded('product'))
            ->map(function ($item) {
                $product = $item->product;

                return [
                    'id'          => $product->id,
                    'combo_id'    => $item->id,
                    'name_uz'     => $product->name_uz,
                    'name_ru'     => $product->name_ru,
                    'name_en'     => $product->name_en,
                    'image_path'  => $product->generateImages(),
                    'extra_price' => $item->extra_price,
                ];
            })
            ->values()
            ->toArray();
    }
}
