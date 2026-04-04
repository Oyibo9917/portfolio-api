<?php
namespace App\Validators;

use App\Enums\ProductCategories;
use App\Enums\InventoryStatus;
use App\Validators\BaseValidator;
use Illuminate\Validation\Rule;

class ProductValidator extends BaseValidator
{
    public function rules(): array
    {
        return [
            // 'code' => [
            //     'required',
            //     'string',
            //     'max:10', // Limit code to 10 characters
            //     'unique:products,code', // Ensure the code is unique in the 'products' table
            // ],
            'name' => [
                'required',
                'string',
                'min:3', // Minimum length of 3 characters
                'max:255', // Maximum length of 255 characters
            ],
            'description' => [
                'required',
                'string',
                'min:10', // Minimum 10 characters
                'max:1000', // Maximum length of 1000 characters
            ],
            'image' => [
                'nullable',
                'string',
                'max:255', // Maximum length of the file path or image name
                'regex:/\.(jpg|jpeg|png|gif)$/i', // Validating image extension (jpg, jpeg, png, gif)
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.01', // Price must be a positive number
                'max:99999.99', // Maximum value for price (you can adjust this limit)
            ],
            'category' => [
                'required',
                'string',
                Rule::in(ProductCategories::values()), // Only allow these values
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1', // Quantity must be at least 1
            ],
            'inventory_status' => [
                'required',
                Rule::in(InventoryStatus::values()), // Use the Enum values for inventory status validation
            ],
            // 'rating' => [
            //     'required',
            //     'integer',
            //     'min:1', // Rating must be at least 1
            //     'max:5', // Rating must not exceed 5
            // ],
        ];
    }
}
