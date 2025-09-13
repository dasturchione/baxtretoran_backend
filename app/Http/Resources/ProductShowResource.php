<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductShowResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name_uz'       => $this->name_uz,
            'name_ru'       => $this->name_ru,
            'name_en'       => $this->name_en,
            'ingredient_uz' => $this->ingredient_uz,
            'ingredient_ru' => $this->ingredient_ru,
            'ingredient_en' => $this->ingredient_en,
            'keywords_uz' => $this->keywords_uz,
            'keywords_ru' => $this->keywords_ru,
            'keywords_en' => $this->keywords_en,
            'modifier'      => $this->modifiers,
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
            ->filter(fn($item) => $item->relationLoaded('product') && $item->product->relationLoaded('category'))
            ->groupBy(fn($item) => $item->product->category->id)
            ->map(function ($items, $categoryId) {
                $category = $items->first()->product->category;

                return [
                    'category_id' => $category->id,
                    'name_uz'     => $category->name_uz,
                    'name_ru'     => $category->name_ru,
                    'name_en'     => $category->name_en,
                    'items'       => $items->map(function ($item) {
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
                    })->values(),
                ];
            })
            ->values()
            ->toArray();
    }
}
