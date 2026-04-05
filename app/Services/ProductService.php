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

    // Track collection cache keys so we can bust them without tagging
    private const COLLECTION_KEY = 'products_collection';
    private const VERSION_KEY    = 'products_cache_version';

    /**
     * Get current cache version — bump this to invalidate all collection caches.
     */
    private function cacheVersion(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    private function bumpCacheVersion(): void
    {
        Cache::put(self::VERSION_KEY, $this->cacheVersion() + 1, 86400);
    }

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
        $cacheKey = self::COLLECTION_KEY . '_v' . $this->cacheVersion() . '_' . md5(json_encode($options));

        $data = Cache::remember($cacheKey, 300, function () use ($options) {
            return $this->repository->getCollection($options)->getData(true);
        });

        return ApiResponse::success($data['data'], $data['message'] ?? 'Records fetched successfully.');
    }

    public function get(int|string $id): JsonResponse
    {
        $cacheKey = "product_{$id}_v" . $this->cacheVersion();

        $data = Cache::remember($cacheKey, 600, function () use ($id) {
            return $this->repository->get($id)->getData(true);
        });

        return ApiResponse::success($data['data'], $data['message'] ?? 'Record fetched successfully.');
    }

    public function create(array $data): JsonResponse
    {
        $this->validator->validate($data);

        DB::beginTransaction();
        try {
            if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['image'] = $data['image']->store('images', 'public');
            } else {
                unset($data['image']);
            }

            $result = parent::create($data);
            DB::commit();
            $this->clearCollectionCache();

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
            Cache::forget("product_{$id}");
            $this->clearCollectionCache();

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
            Cache::forget("product_{$id}");
            $this->clearCollectionCache();

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
            foreach ($ids as $id) {
                Cache::forget("product_{$id}");
            }
            $this->clearCollectionCache();

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
            Cache::forget("product_{$data['product_id']}");
            $this->clearCollectionCache();

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
            Cache::forget("product_{$productId}");
            $this->clearCollectionCache();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    /**
     * Bump the cache version to invalidate all collection and individual product caches.
     */
    private function clearCollectionCache(): void
    {
        $this->bumpCacheVersion();
    }
}
