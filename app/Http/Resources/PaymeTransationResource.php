<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymeTransationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->paycom_transaction_id,
            'time' => $this->paycom_time,
            'amount' => $this->amount,
            'account' => [
                'order_id' => $this->order_id,
            ],
            'create_time' => intval($this->paycom_time),
            'perform_time' => intval($this->perform_time_unix),
            'cancel_time' => intval($this->cancel_time) ?? 0,
            'transaction' => $this->id,
            'state' => $this->state,
            'reason' => $this->reason,
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
