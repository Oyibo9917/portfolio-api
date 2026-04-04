<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeatherData extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_name',
        'country_code',
        'lat',
        'lon',
        'weather_main',
        'weather_description',
        'weather_icon',
        'temp',
        'feels_like',
        'humidity',
        'pressure',
        'visibility',
        'wind_speed',
        'wind_deg',
        'wind_gust',
        'clouds_all',
        'sunrise',
        'sunset',
    ];

    // Optionally, you can cast some attributes to specific types
    protected $casts = [
        'lat' => 'decimal:6',
        'lon' => 'decimal:6',
        'temp' => 'decimal:2',
        'feels_like' => 'decimal:2',
        'humidity' => 'integer',
        'pressure' => 'integer',
        'visibility' => 'integer',
        'wind_speed' => 'decimal:2',
        'wind_deg' => 'decimal:2',
        'wind_gust' => 'decimal:2',
        'clouds_all' => 'integer',
        'sunrise' => 'integer',
        'sunset' => 'integer',
    ];
}
