<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionHistoryRequest extends FormRequest
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
            'type' => [
                'sometimes',
                'string',
                Rule::in(TransactionType::cases()),
            ],
            'date_from' => 'sometimes|date|date_format:Y-m-d',
            'date_to' => 'sometimes|date|date_format:Y-m-d|after_or_equal:date_from',
        ];
    }
}

