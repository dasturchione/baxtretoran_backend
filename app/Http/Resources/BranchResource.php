<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'long'              => $this->long,
            'lat'               => $this->lat,
            'work_time_start'   => $this->work_time_start,
            'work_time_end'     => $this->work_time_end,
            'is_active'         => $this->is_active
        ];
    }
}
