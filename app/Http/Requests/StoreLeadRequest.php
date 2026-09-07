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
            'phone' => ['required', 'string', 'regex:/^\+7\d{10}$/'],
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
            'phone.required' => 'Введите телефон',
            'phone.regex' => 'Введите телефон',
            'price.required' => 'Введите цену',
            'price.numeric' => 'Введите цену',
            'price.gt' => 'Цена должна быть положительным числом',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => $this->normalizePhone((string) $this->input('phone')),
            ]);
        }
    }

    /**
     * Канонический формат: +7XXXXXXXXXX.
     */
    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            return '+7'.substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '+7'.$digits;
        }

        return trim($value);
    }
}
