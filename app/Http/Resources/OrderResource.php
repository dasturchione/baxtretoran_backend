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
            'id'            => $this->id,
            'user'          => new UserResource($this->user),
            'delivery_type' => $this->delivery_type,
            'address'       => new AddressResource($this->address),
            'status'        => $this->status,
            'paymentMethod' => $this->paymentMethod,
            'created_at'    => format_date($this->created_at, "d.m.Y H:i"),
            'total_price'   => $this->total_price,
            'items'         => $this->items->map(function ($item) {
                return [
                    'id'            => $item->product->id,
                    'name_uz'       => $item->product->name_uz,
                    'name_ru'       => $item->product->name_ru,
                    'name_en'       => $item->product->name_en,
                    'images'        => $item->product->generateImages(),
                    'quantity'      => $item->quantity,
                    'combo_items'   => $item->product->comboItems,
                    'price'         => $item->product->price,
                    'total'         => $item->quantity * $item->product->price,
                ];
            }),
        ];
    }
}
