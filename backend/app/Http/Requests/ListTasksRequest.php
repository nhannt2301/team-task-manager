<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:todo,in_progress,done'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'cursor' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedParameters = ['status', 'search', 'cursor'];

            if (array_diff(array_keys($this->query()), $allowedParameters) !== []) {
                $validator->errors()->add(
                    'query',
                    'Only status, search, and cursor query parameters are allowed.',
                );
            }
        });
    }
}
