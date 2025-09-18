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
            'branch'        => new BranchResource($this->branch),
            'address'       => new AddressResource($this->address),
            'histories'     => $this->histories,
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
                    'type'          => $item->product->type,
                    'images'        => $item->product->generateImages(),
                    'quantity'      => $item->quantity,
                    'combo_items'   => $item->combo_items_detailed,
                    'price'         => $item->price,
                    'total'         => $item->quantity * $item->price,
                ];
            }),
        ];
    }
}
