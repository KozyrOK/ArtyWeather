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
        Schema::create('weather_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 8, 5);
            $table->decimal('longitude', 8, 5);
            $table->smallInteger('forecast_period');
            $table->boolean('temperature')->default(true);
            $table->boolean('apparent_temperature')->default(true);
            $table->boolean('relative_humidity')->default(true);
            $table->boolean('precipitation')->default(true);
            $table->boolean('weather_code')->default(true);
            $table->boolean('cloud_cover')->default(true);
            $table->boolean('pressure')->default(true);
            $table->boolean('wind_speed')->default(true);
            $table->boolean('wind_direction')->default(true);
            $table->boolean('wind_gusts')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_settings');
    }
};
