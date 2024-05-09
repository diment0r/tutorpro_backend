<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class PremiumPurchaseRequest extends FormRequest
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
            'card' => ['required', 'regex:/[0-9]{13,16}/'],
            'cvv' => ['required', 'regex:/[0-9]{3}/'],
            'date_of_expiration' => ['required', 'date_format:m/y'],
        ];
    }
}
