<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    protected array $relation = [];
    protected string $createRelation = '';
    protected ?Model $modelInstance = null;

    public function setModel(Model $model): self
    {
        $this->modelInstance = $model;
        return $this;
    }

    public function getModel(): Model
    {
        return $this->modelInstance ?? new Product();
    }

    public function setQuery()
    {
        $user = Auth::user();
        $isLoggedIn = Auth::check();

        $this->query = $this->getModel()
            ->with([
                'activityLogs' => function ($q) use ($user, $isLoggedIn) {
                    $q->with('user:id,name,email')
                        ->select('id', 'user_id', 'action', 'subject_id', 'created_at', 'changes')
                        ->orderBy('created_at', 'desc');

                    if ($isLoggedIn) {
                        $q->where('user_id', $user->id);
                    }
                },
                'ratings' => function ($q) use ($user, $isLoggedIn) {
                    $q->with('user:id,uuid,name,email')
                        ->select('user_id', 'product_id', 'rating', 'comment', 'created_at');

                    if ($isLoggedIn && $user->hasRole('user')) {
                        $q->where('user_id', $user->id);
                    }

                    $q->orderBy('created_at', 'desc');
                }
            ]);

        return $this->query;
    }

    public function getCollection(array $options): JsonResponse
    {
        $this->query = $this->getQuery();
        $this->applyWith($options);

        // Use eager loading with aggregates to avoid N+1
        $products = $this->query
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->get();

        $productData = $products->map(function ($product) {
            $data = $product->toArray();
            $data['product_rating'] = [
                'average' => round($product->ratings_avg_rating ?? 0, 1),
                'count' => $product->ratings_count ?? 0,
            ];
            return $data;
        });

        return ApiResponse::success($productData, "Records fetched successfully.", 200);
    }

    public function rating(array $data): JsonResponse
    {
        $this->createRelation = 'ratings';

        $product = Product::findOrFail($data['product_id']);
        $this->setModel($product);

        return $this->create([
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'user_id' => $data['user_id'],
        ]);
    }

    public function getProductRatings(int $productId): array
    {
        // Use aggregate functions instead of loading all ratings
        $stats = Rating::where('product_id', $productId)
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        return [
            'average' => round($stats->average ?? 0, 1),
            'count' => $stats->count ?? 0,
        ];
    }

    public function uploadImage(int $productId, $image): JsonResponse
    {
        $product = Product::findOrFail($productId);

        // Delete old image if exists
        if ($product->image) {
            $this->deleteImage($product);
        }

        // Store new image
        $path = $image->store('images', 'public');
        $product->update(['image' => $path]);

        return ApiResponse::success($product, "Image uploaded successfully.", 200);
    }

    protected function getPermittedIds(array $ids, int $userId): array
    {
        // Get product IDs that user has activity logs for
        return DB::table('activity_logs')
            ->where('subject_type', Product::class)
            ->whereIn('subject_id', $ids)
            ->where('user_id', $userId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();
    }

    protected function canDelete(int $id, int $userId): bool
    {
        // Check if user has activity logs for this product
        return DB::table('activity_logs')
            ->where('subject_type', Product::class)
            ->where('subject_id', $id)
            ->where('user_id', $userId)
            ->exists();
    }
}
