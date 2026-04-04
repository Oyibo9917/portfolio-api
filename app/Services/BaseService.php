<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Validators\BaseValidator;
use Illuminate\Http\JsonResponse;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository, BaseValidator $validator = null)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): JsonResponse
    {
        $data = array_merge($data, [
            'user_id' => auth()->id(),
        ]);

        return $this->repository->create($data);
    }

    public function update(int $id, $data): JsonResponse
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): JsonResponse
    {
        return $this->repository->delete($id);
    }

    public function deleteMultiple(array $ids): JsonResponse
    {
        return $this->repository->deleteMultiple($ids);
    }

    /**
     * @throws Exception
     */
    public function get(int|string $id): JsonResponse
    {
        return $this->repository->get($id);
    }

    public function getCollection(array $options): JsonResponse
    {
        return $this->repository->getCollection($options);
    }
}
