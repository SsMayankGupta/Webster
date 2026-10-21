<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('to_date');
        });

        Schema::table('rebate_ledgers', function (Blueprint $table) {
            $table->unsignedBigInteger('amount_paise')->nullable()->after('amount');
        });

        Schema::table('rebate_ledgers', function (Blueprint $table) {
            $table->unique('leave_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('rebate_ledgers', function (Blueprint $table) {
            $table->dropUnique(['leave_request_id']);
            $table->dropColumn('amount_paise');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
