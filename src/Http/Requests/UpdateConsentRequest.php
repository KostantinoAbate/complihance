<?php

namespace KostantinoAbate\Complihance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array'],
            'categories.*' => ['string'],

            'vendors' => ['nullable', 'array'],
            'vendors.*' => ['boolean'],
        ];
    }
}
