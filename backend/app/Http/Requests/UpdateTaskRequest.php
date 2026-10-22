<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
        $rules = [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:todo,in_progress,done'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];

        $rules['assigned_to'] = $this->user()?->isAdmin() === true
            ? ['sometimes', 'required', 'integer', 'exists:users,id']
            : ['prohibited'];

        return $rules;
    }
}
