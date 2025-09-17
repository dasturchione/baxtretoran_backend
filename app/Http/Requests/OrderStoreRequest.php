<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id'),
            ],
            'delivery_type'     => ['required', Rule::in(['delivery', 'takeaway'])],

            'address_id' => [
                Rule::requiredIf(fn() => $this->delivery_type === 'delivery'),
                Rule::excludeIf(fn() => $this->delivery_type === 'takeaway'),
                Rule::exists('user_addresses', 'id')
                    ->where(fn($q) => $q->where('user_id', Auth::id())),
            ],

            'branch_id' => [
                Rule::requiredIf(fn() => $this->delivery_type === 'takeaway'),
                Rule::excludeIf(fn() => $this->delivery_type === 'delivery'),
                Rule::exists('branches', 'id'),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],

            'items.*.combo_items'   => ['nullable', 'array'],
            'items.*.combo_items.*' => [Rule::exists('products', 'id')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->items ?? [] as $index => $item) {
                $product = app('App\\Models\\Product')::find($item['product_id'] ?? null);

                if ($product && $product->type === 'combo') {
                    if (empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "combo_items is required for combo products.");
                    }
                } else {
                    if (!empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "A normal product should not contain combo_items.");
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->delivery_type === 'delivery') {
            $this->merge(['branch_id' => null]);
        }

        if ($this->delivery_type === 'takeaway') {
            $this->merge(['address_id' => null]);
        }
    }
}
