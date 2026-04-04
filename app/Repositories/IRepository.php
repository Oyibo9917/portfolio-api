<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
interface IRepository {
    public function create(array $data): JsonResponse;

    public function update(int $id, array $data): JsonResponse;

    public function delete(int $ids): JsonResponse;

    public function get(int|string $id): JsonResponse;

    public function getAll(array $options): JsonResponse;

    public function getCollection(array $options): JsonResponse;

    public function getModel(): Model|null;
}
