<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->string('city_name');
            $table->string('country_code', 2);
            $table->decimal('lat', 10, 6);
            $table->decimal('lon', 10, 6);
            $table->string('weather_main');
            $table->string('weather_description');
            $table->string('weather_icon');
            $table->decimal('temp', 5, 2);
            $table->decimal('feels_like', 5, 2);
            $table->integer('humidity');
            $table->integer('pressure');
            $table->integer('visibility');
            $table->decimal('wind_speed', 5, 2);
            $table->decimal('wind_deg', 5, 2);
            $table->decimal('wind_gust', 5, 2);
            $table->integer('clouds_all');
            $table->integer('sunrise');
            $table->integer('sunset');
            $table->timestamps();
            $table->index(['city_name', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
