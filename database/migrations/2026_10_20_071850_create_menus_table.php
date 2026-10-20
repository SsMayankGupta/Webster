<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->enum('meal_type', ['breakfast', 'lunch', 'snacks', 'dinner']);
            $table->dateTime('serve_at');
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('carbs', 8, 2)->default(0);
            $table->decimal('calories', 8, 2)->default(0);
            $table->text('recipe_text')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->enum('status', ['open', 'locked', 'serving', 'closed'])->default('open');
            $table->unsignedBigInteger('headcount_locked')->nullable();
            $table->timestamps();
            $table->index('serve_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
