<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

abstract class BaseRepository implements IRepository
{
    protected array $relation = [];
    protected string $createRelation = '';
    protected ?string $API_URL = null;
    protected ?string $API_KEY = null;
    protected $query;

    /**
     * Get query builder instance
     */
    public function getQuery(): Relation|Builder
    {
        if (!$this->query && method_exists($this, 'setQuery')) {
            return $this->setQuery();
        }

        return $this->getModel()->newQuery();
    }

    public function create(array $data): JsonResponse
    {
        $model = $this->getModel();
        
        if (!empty($this->createRelation) && method_exists($model, $this->createRelation)) {
            $record = $model->{$this->createRelation}()->create($data);
        } else {
            $record = $model->create($data);
        }

        return ApiResponse::success($record, "Record created successfully.", 201);
    }

    public function update(int $id, array $data): JsonResponse
    {
        $model = $this->getModel()->findOrFail($id);

        if (isset($data['image']) && $data['image']) {
            $this->deleteImage($model);
        }

        $model->update($data);
        $model->refresh();

        return ApiResponse::success($model, "Record updated successfully.", 200);
    }

    public function delete(int $id): JsonResponse
    {
        $model = $this->getModel()->findOrFail($id);
        $authUserId = Auth::id();
        
        // Check permissions
        if (!$this->canDelete($id, $authUserId)) {
            return ApiResponse::error("You do not have permission to delete this record.", null, 403);
        }

        if (isset($model->image) && $model->image) {
            $this->deleteImage($model);
        }

        $model->delete();

        return ApiResponse::success(null, "Record deleted successfully.", 200);
    }

    public function deleteMultiple(array $ids): JsonResponse
    {
        $authUserId = Auth::id();
        $permittedIds = $this->getPermittedIds($ids, $authUserId);
        
        if (count($permittedIds) !== count($ids)) {
            return ApiResponse::error("You do not have permission to delete some of these records.", null, 403);
        }

        $models = $this->getModel()->whereIn('id', $ids)->get();

        if ($models->isEmpty()) {
            return ApiResponse::error("No records found with the provided IDs.", null, 404);
        }

        // Delete images if applicable
        foreach ($models as $model) {
            if (isset($model->image) && $model->image) {
                $this->deleteImage($model);
            }
        }

        $this->getModel()->whereIn('id', $ids)->delete();

        return ApiResponse::success(null, "Records deleted successfully.", 200);
    }

    public function get(int|string $id): JsonResponse
    {
        $query = $this->getModel()->newQuery();

        if (!empty($this->relation)) {
            $query->with($this->relation);
        }

        $model = $query->findOrFail($id);

        return ApiResponse::success($model, "Record fetched successfully.", 200);
    }

    public function getCollection(array $options): JsonResponse
    {
        $this->query = $this->getQuery();
        $this->applyWith($options);

        $data = $this->query->get();

        return ApiResponse::success($data, "Records fetched successfully.", 200);
    }

    protected function applyWith(array $options): void
    {
        $with = array_merge($options['with'] ?? [], $this->relation);
        if (!empty($with)) {
            $this->query->with($with);
        }
    }

    public function getAll(array $options = []): JsonResponse
    {
        $query = $this->getModel()->newQuery();

        if (!empty($this->relation)) {
            $query->with($this->relation);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return ApiResponse::success($data, "Records fetched successfully.", 200);
    }

    protected function applyFilters(array $options)
    {
        $transformed = [];
        $filters = $options['filters'] ?? [];

        if (!empty($transformed)) {
            $filters = $transformed;

            $this->query->where(function ($q) use ($filters) {//where ORs
                foreach ($filters as $k => $filter) {
                    if (!isset($filter['field'])) {
                        continue;
                    }

                    if ($filter['field'] == 'title') {
                        $q->orWhere(
                            'title',
                            'LIKE',
                            "%" . str_replace(' ', '%', $filter['value']) . "%"
                        );
                    }

                    if ($filter['field'] == 'content') {
                        $q->orWhere(
                            'title',
                            'LIKE',
                            "%" . str_replace(' ', '%', $filter['value']) . "%"
                        );
                    }
                }
            })->where(function ($q) use ($filters) {//where ANDs

                foreach ($filters as $k => $filter) {
                    if (!isset($filter['field'])) {
                        continue;
                    }

                    if ($filter['field'] == 'community_id' && $filter['value']) {
                        if (is_array($filter['value'])) {
                            $q->whereIn('community_id', $filter['value']);
                        } else {
                            $q->where('community_id', $filter['value']);
                        }
                    }
                }
            });
        }
    }

    protected function deleteImage($model): void
    {
        $imagePath = $model->getRawOriginal('image');

        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    protected function canDelete(int $id, int $userId): bool
    {
        // Override in child repositories for specific permission logic
        return true;
    }

    protected function getPermittedIds(array $ids, int $userId): array
    {
        // Override in child repositories for specific permission logic
        return $ids;
    }

    /**
     * Get authenticated user by UUID
     */
    public function getAuthUser(?string $uuid = null): User
    {
        if (!$uuid) {
            throw new AuthenticationException('User UUID is required');
        }

        $user = User::where('uuid', $uuid)->first();
        
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        return $user;
    }
}
