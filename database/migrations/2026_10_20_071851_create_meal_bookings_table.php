<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->enum('status', ['booked', 'cancelled', 'consumed'])->default('booked');
            $table->dateTime('locked_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_bookings');
    }
};
