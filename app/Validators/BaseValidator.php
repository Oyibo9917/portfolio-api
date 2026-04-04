<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseValidator
{
    protected array $data = [];
    protected abstract function rules(): array;

    public function validate(array $data, array $rules = []): void
    {
        $this->data = $data;
        $validator = Validator::make(
            $data, !empty($rules) ? $rules : $this->rules(),
            $this->messages()
        );

        // Check if validation failed and throw an exception
        if ($validator->fails()) {
            // Return only the first error message
            throw new ValidationException($validator, response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'error' => $validator->errors()->first(),  // Only the first error message
            ], 422));
        }
    }

    public function messages(): array
    {
        return [];
    }

    public function input(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
