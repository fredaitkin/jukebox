<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Closure;

class RunQueryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('super-user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'myquery' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! isValidReadQuery($value)) {
                        $fail('Only single read-only SELECT, SHOW, DESCRIBE, or EXPLAIN queries are allowed.');
                    }
                },
            ],
            'show_cols' => ['nullable', 'accepted'],
        ];
    }
}
