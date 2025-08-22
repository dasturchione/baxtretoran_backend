<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductShowResource extends JsonResource
{
    public function toArray($request)
    {

        // Guruhlash
        $groups = [];
        foreach ($this->comboItems as $item) {
            $cat = $item->product->category;
            $catId = $cat->id;

            if (!isset($groups[$catId])) {
                $groups[$catId] = [
                    'category_id' => $cat->id,
                    'name_uz'     => $cat->name_uz,
                    'name_ru'     => $cat->name_ru,
                    'name_en'     => $cat->name_en,
                    'items'       => [],
                ];
            }

            $groups[$catId]['items'][] = [
                'id'            => $item->product->id,
                'combo_id'      => $item->id,
                'name_uz'       => $item->product->name_uz,
                'name_ru'       => $item->product->name_ru,
                'name_en'       => $item->product->name_en,
                'image_path'    => $this->image_path ? asset('storage/' . $this->image_path) : null,
                'extra_price'   => $item->extra_price,
            ];
        }

        return [
            'id'            => $this->id,
            'name_uz'       => $this->name_uz,
            'name_ru'       => $this->name_ru,
            'name_en'       => $this->name_en,
            'ingredient_uz' => $this->ingredient_uz,
            'ingredient_ru' => $this->ingredient_ru,
            'ingredient_en' => $this->ingredient_en,
            'modifier'      => $this->modifiers,
            'price'         => $this->price,
            'image_path'    => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'type'          => $this->type,
            'combo_items'   => array_values($groups),
        ];
    }
}
