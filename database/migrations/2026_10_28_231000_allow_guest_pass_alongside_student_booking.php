<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_bookings', function (Blueprint $table): void {
            $table->dropUnique(['student_id', 'menu_id']);
            $table->unique(['student_id', 'menu_id', 'is_guest_pass']);
        });
    }

    public function down(): void
    {
        Schema::table('meal_bookings', function (Blueprint $table): void {
            $table->dropUnique(['student_id', 'menu_id', 'is_guest_pass']);
            $table->unique(['student_id', 'menu_id']);
        });
    }
};
