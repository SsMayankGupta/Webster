<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('semester_name', 100)->nullable();
            $table->date('semester_starts_at')->nullable();
            $table->date('semester_ends_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['semester_name', 'semester_starts_at', 'semester_ends_at']);
        });
    }
};
