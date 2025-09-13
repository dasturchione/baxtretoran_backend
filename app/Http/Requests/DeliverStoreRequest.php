<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'telegram_id' => 'required|numeric|unique:delivers,telegram_id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'image_path'  => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
