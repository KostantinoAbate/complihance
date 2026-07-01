<?php

namespace KostantinoAbate\Complihance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsentRequest extends FormRequest
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
