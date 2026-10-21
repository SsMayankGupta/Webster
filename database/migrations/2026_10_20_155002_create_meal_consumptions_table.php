<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('meal_bookings')->cascadeOnDelete();
            $table->uuid('jti')->nullable()->index();
            $table->string('lane')->nullable();
            $table->enum('mode', ['online', 'offline'])->default('online');
            $table->string('device_id')->nullable();
            $table->timestamp('consumed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_consumptions');
    }
};
