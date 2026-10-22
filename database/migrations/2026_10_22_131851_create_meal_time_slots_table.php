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
        Schema::create('meal_time_slots', function (Blueprint $table) {
            $table->id();
            $table->enum('meal_type', ['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER']);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('cutoff_hours_before')->default(4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_time_slots');
    }
};
