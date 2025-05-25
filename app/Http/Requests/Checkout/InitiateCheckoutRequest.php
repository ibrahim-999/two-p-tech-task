<?php
// app/Http/Requests/InitiateCheckoutRequest.php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class InitiateCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:2'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Customer name is required',
            'email.required' => 'Customer email is required',
            'email.email' => 'Please provide a valid email address'
        ];
    }
}
