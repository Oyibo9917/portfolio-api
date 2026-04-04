<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\WeatherData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ApiResponse;

class WeatherApiRepository extends BaseRepository
{
    protected array $relation = [];
    protected ?string $API_URL = 'WEATHER_API_URL';
    protected ?string $API_KEY = 'WEATHER_API_KEY';

    public function getModel(): Model|null
    {
        return new User();
    }

    public function get(int|string $city): JsonResponse
    {
        if (empty($city) || $city === 'default') {
            if (auth()->check()) {
                $city = auth()->user()->profile->city ?? 'lagos';
            } else {
                $city = 'lagos';
            }
        }

        $weather = WeatherData::where('city_name', $city)->first();

        if ($weather && !$weather->created_at->isToday()) $weather->delete();

        if ($weather) return ApiResponse::success($this->formatWeatherData($weather), "Record fetched successfully.", 200);

        $apiKey = env($this->API_KEY);
        $apiUrl = env($this->API_URL);

        $response = Http::get($apiUrl, [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->createWeatherDataRecord($data);

            return ApiResponse::success($data, "Record fetched successfully.", 200);
        } else {
            throw new \Exception("Failed to fetch weather data from API.");
        }
    }

    private function createWeatherDataRecord($data): void
    {
        $weatherData = new WeatherData();
        $weatherData->city_name = $data['name'] ?? '';
        $weatherData->country_code = $data['sys']['country'] ?? '';
        $weatherData->lat = $data['coord']['lat'] ?? 0;
        $weatherData->lon = $data['coord']['lon'] ?? 0;
        $weatherData->weather_main = $data['weather'][0]['main'] ?? '';
        $weatherData->weather_description = $data['weather'][0]['description'] ?? '';
        $weatherData->weather_icon = $data['weather'][0]['icon'] ?? '';
        $weatherData->temp = $data['main']['temp'] ?? 0;
        $weatherData->feels_like = $data['main']['feels_like'] ?? 0;
        $weatherData->humidity = $data['main']['humidity'] ?? 0;
        $weatherData->pressure = $data['main']['pressure'] ?? 0;
        $weatherData->visibility = $data['visibility'] ?? 0;
        $weatherData->wind_speed = $data['wind']['speed'] ?? 0;
        $weatherData->wind_deg = $data['wind']['deg'] ?? 0;
        $weatherData->wind_gust = $data['wind']['gust'] ?? 0;
        $weatherData->clouds_all = $data['clouds']['all'] ?? 0;
        $weatherData->sunrise = $data['sys']['sunrise'] ?? 0;
        $weatherData->sunset = $data['sys']['sunset'] ?? 0;

        $weatherData->save();
    }

    private function formatWeatherData($weatherData)
    {
        return [
            'coord' => [
                'lon' => $weatherData->lon,
                'lat' => $weatherData->lat,
            ],
            'weather' => [
                [
                    'id' => $weatherData->id,
                    'main' => $weatherData->weather_main,
                    'description' => $weatherData->weather_description,
                    'icon' => $weatherData->weather_icon,
                ]
            ],
            'base' => 'stations',
            'main' => [
                'temp' => $weatherData->temp,
                'feels_like' => $weatherData->feels_like,
                'temp_min' => $weatherData->temp,
                'temp_max' => $weatherData->temp,
                'pressure' => $weatherData->pressure,
                'humidity' => $weatherData->humidity,
                'sea_level' => 1012,
                'grnd_level' => 1011,
            ],
            'visibility' => $weatherData->visibility,
            'wind' => [
                'speed' => $weatherData->wind_speed,
                'deg' => $weatherData->wind_deg,
                'gust' => $weatherData->wind_gust,
            ],
            'clouds' => [
                'all' => $weatherData->clouds_all,
            ],
            'dt' => time(),
            'sys' => [
                'country' => $weatherData->country_code,
                'sunrise' => $weatherData->sunrise,
                'sunset' => $weatherData->sunset,
            ],
            'timezone' => 3600,
            'id' => 2323675,
            'name' => $weatherData->city_name,
            'cod' => 200,
        ];
    }
}
