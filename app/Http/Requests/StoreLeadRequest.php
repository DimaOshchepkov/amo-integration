<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'string', 'email'],
            'phone' => ['required', 'string', 'min:5'],
            'price' => ['required', 'numeric', 'gt:0'],
            'spent_more_than_30_seconds' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Минимум 2 символа',
            'email.email' => 'Некорректный email',
            'phone.min' => 'Введите телефон',
            'price.required' => 'Введите цену',
            'price.numeric' => 'Введите цену',
            'price.gt' => 'Цена должна быть положительным числом',
        ];
    }
}
