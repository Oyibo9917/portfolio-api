<?php

namespace App\Validators;

use App\Validators\BaseValidator;


class RatingValidator extends BaseValidator
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Optional: Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'rating.required' => 'Please provide a rating.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating cannot exceed 5.',
            'review.max' => 'Review cannot be longer than 1000 characters.',
        ];
    }
}
