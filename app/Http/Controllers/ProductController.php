<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->service = $productService;
        $this->productService = $productService;
        parent::__construct();
    }

    public function deleteMultiple(Request $request): JsonResponse
    {
        $ids = $request->input('data', []);
        
        return $this->productService->deleteMultiple($ids);
    }

    public function rating(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['user_id'] = auth()->id();

        return $this->productService->addRating($data);
    }

    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        return $this->productService->uploadImage($id, $request->file('image'));
    }
}
