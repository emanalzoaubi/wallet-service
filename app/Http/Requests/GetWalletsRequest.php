<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetWalletsRequest extends FormRequest
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
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'owner_name' => 'sometimes|string|max:255',
            'currency' => [
                'sometimes',
                'string',
                Rule::enum(Currency::class),
            ],
        ];
    }
}
