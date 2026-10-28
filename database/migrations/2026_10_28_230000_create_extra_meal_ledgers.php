<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_meal_charges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('meal_bookings')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('guest_count');
            $table->unsignedBigInteger('price_per_guest_paise');
            $table->unsignedBigInteger('amount_paise');
            $table->timestamps();
            $table->index(['student_id', 'created_at']);
        });

        Schema::create('extra_meal_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->timestamps();
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_meal_collections');
        Schema::dropIfExists('extra_meal_charges');
    }
};
