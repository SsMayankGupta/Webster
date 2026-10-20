<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('jti');
            $table->string('device_id');
            $table->string('hmac');
            $table->boolean('is_offline')->default(false);
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
            $table->index(['jti', 'device_id']);
            $table->foreign('jti')->references('jti')->on('qr_tokens')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
