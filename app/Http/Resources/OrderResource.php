<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => $this->user,
            'address'     => new AddressResource($this->address),
            'status'      => $this->status,
            'created_at'  => date_format_short($this->created_at),
            'total_price' => $this->total_price,
            'items' => $this->items->map(function ($item) {
                return [
                    'id'   => $item->product->id,
                    'name_uz' => $item->product->name_uz,
                    'name_ru' => $item->product->name_ru,
                    'name_en' => $item->product->name_en,
                    'image_path' => $item->product->image_path
                        ? asset('storage/' . $item->product->image_path)
                        : null,
                    'quantity'     => $item->quantity,
                    'combo_items'  => $item->product->comboItems,
                    'price'        => $item->product->price,
                    'total'        => $item->quantity * $item->product->price,
                ];
            }),
        ];
    }
}
