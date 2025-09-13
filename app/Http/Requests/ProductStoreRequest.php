<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'name_uz' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',

            'ingredient_uz' => 'required|string',
            'ingredient_ru' => 'required|string',
            'ingredient_en' => 'required|string',

            'keywords_uz' => 'required|string',
            'keywords_ru' => 'required|string',
            'keywords_en' => 'required|string',

            'price' => 'required|numeric',

            // ✅ Fayl uchun ruxsat berilgan rasm formatlari (jpg, jpeg, png, webp va h.k.)
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',

            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:simple,combo',

            'ikpu_code' => 'required|string|max:50',
            'package_code' => 'required|string|max:50',
            'vat_percent' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',

            // ✅ modifiers — array bo'lishi kerak, ichida modifier_id lar bo'ladi
            'modifiers' => 'sometimes|array',
            'modifiers.*.modifier_id' => 'required|integer|exists:modifiers,id',

            // ✅ combo_items — array bo'lishi kerak, ichida product_id lar bo'ladi
            'combo_items' => 'sometimes|array',
            'combo_items.*.product_id' => 'required|integer|exists:products,id',
        ];
    }
}
