<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'telegram_id' => 'required|numeric|unique:delivers,telegram_id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'image_path'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
