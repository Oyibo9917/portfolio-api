<?php

namespace App\Services;

use App\Repositories\WeatherApiRepository;
use Illuminate\Http\JsonResponse;

class WeatherService extends BaseService
{
    public function __construct(WeatherApiRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getWeather(string $city): JsonResponse
    {
        return $this->repository->get($city);
    }
}
