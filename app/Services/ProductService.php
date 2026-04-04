<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Repositories\ProductRepository;
use App\Validators\ProductValidator;
use App\Validators\RatingValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    protected RatingValidator $ratingValidator;

    public function __construct(
        ProductRepository $repository,
        ProductValidator $validator,
        RatingValidator $ratingValidator
    ) {
        parent::__construct($repository, $validator);
        $this->ratingValidator = $ratingValidator;
    }

    public function getCollection(array $options): JsonResponse
    {
        // Cache product list for 5 minutes
        $cacheKey = 'products_' . md5(json_encode($options));
        
        return Cache::remember($cacheKey, 300, function () use ($options) {
            return $this->repository->getCollection($options);
        });
    }

    public function get(int|string $id): JsonResponse
    {
        // Cache individual product for 10 minutes
        $cacheKey = "product_{$id}";
        
        return Cache::remember($cacheKey, 600, function () use ($id) {
            return $this->repository->get($id);
        });
    }

    public function create(array $data): JsonResponse
    {
        $this->validator->validate($data);
        
        DB::beginTransaction();
        try {
            $result = parent::create($data);
            DB::commit();
            
            // Clear cache
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function update(int $id, $data): JsonResponse
    {
        $this->validator->validate($data, $this->validator->rules());
        
        DB::beginTransaction();
        try {
            $result = parent::update($id, $data);
            DB::commit();
            
            // Clear specific product cache
            Cache::forget("product_{$id}");
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = parent::delete($id);
            DB::commit();
            
            // Clear cache
            Cache::forget("product_{$id}");
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function deleteMultiple(array $ids): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = parent::deleteMultiple($ids);
            DB::commit();
            
            // Clear cache
            foreach ($ids as $id) {
                Cache::forget("product_{$id}");
            }
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function addRating(array $data): JsonResponse
    {
        $this->ratingValidator->validate($data);
        
        DB::beginTransaction();
        try {
            $result = $this->repository->rating($data);
            DB::commit();
            
            // Clear product cache as rating affects it
            Cache::forget("product_{$data['product_id']}");
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function uploadImage(int $productId, $image): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = $this->repository->uploadImage($productId, $image);
            DB::commit();
            
            // Clear cache
            Cache::forget("product_{$productId}");
            Cache::tags(['products'])->flush();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }
}
