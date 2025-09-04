<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $images = [];
        $sizes = $this->imageSize('image_path'); // modeldagi imageSize() metodini chaqiramiz

        foreach ($sizes as $key => $size) {
            $images[$key] = asset($this->getImage('image_path', $this->image_path, $key));
        }

        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'birthday' => date_format_short($this->birthday),
            'phone'    => $this->phone,
            'images'   => $images, // { 'large': '...', 'original': '...' }
        ];
    }
}
