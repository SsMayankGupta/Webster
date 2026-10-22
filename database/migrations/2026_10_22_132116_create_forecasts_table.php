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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->unsignedInteger('ewma_show_rate')->default(0)->comment('Exponentially Weighted Moving Average show rate');
            $table->unsignedInteger('forecasted_quantity')->default(0);
            $table->unsignedInteger('actual_consumption')->default(0);
            $table->decimal('forecast_accuracy', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
