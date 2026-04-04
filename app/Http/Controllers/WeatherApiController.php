<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

class WeatherApiController extends Controller
{
    public function __construct(WeatherService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    public function show(int|string $city): JsonResponse
    {
        return $this->service->getWeather($city);
    }
}
