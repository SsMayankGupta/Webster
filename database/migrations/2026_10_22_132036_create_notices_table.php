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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('target_group', ['ALL', 'PENDING_FEES', 'HOSTEL_BLOCK'])->default('ALL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
