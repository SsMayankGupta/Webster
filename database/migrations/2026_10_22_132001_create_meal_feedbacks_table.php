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
        Schema::create('meal_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('1-5 rating');
            $table->unsignedTinyInteger('hygiene_rating')->comment('1-5 hygiene rating');
            $table->text('comment')->nullable();
            $table->enum('feedback_type', ['DAILY', 'PERIODIC_15_DAY'])->default('DAILY');
            $table->enum('contractor_action', ['PENDING', 'ACKNOWLEDGED', 'ACTION_TAKEN'])->default('PENDING');
            $table->text('contractor_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_feedbacks');
    }
};
