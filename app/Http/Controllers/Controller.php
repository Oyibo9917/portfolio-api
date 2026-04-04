<?php

namespace App\Http\Controllers;

use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base Controller
 *
 * Provides common CRUD operations for all controllers
 * Controllers extending this class should inject their service in constructor
 */
abstract class Controller
{
    protected ?BaseService $service = null;

    public function __construct()
    {
        // Child controllers should set $this->service before calling parent::__construct()
    }

    /**
     * Get all records
     */
    public function index(Request $request): JsonResponse
    {
        $options = [
            'with' => $request->input('with', []),
            'filters' => $request->input('filters', []),
        ];

        return $this->service->getCollection($options);
    }

    /**
     * Create a new record
     */
    public function store(Request $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    /**
     * Get a single record
     */
    public function show(int|string $id): JsonResponse
    {
        return $this->service->get($id);
    }

    /**
     * Update a record
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    /**
     * Delete a record
     */
    public function destroy(int|string $id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
