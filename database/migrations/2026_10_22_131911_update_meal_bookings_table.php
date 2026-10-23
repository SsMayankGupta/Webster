<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meal_bookings', function (Blueprint $table) {
            $table->boolean('is_guest_pass')->default(false)->after('status');
            $table->foreignId('primary_student_id')->nullable()->constrained('users')->after('is_guest_pass');
            $table->unsignedInteger('guest_count')->default(1)->after('primary_student_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status ENUM('OPTED_IN', 'CANCELLED', 'CONSUMED', 'PENDING_APPROVAL') NOT NULL DEFAULT 'OPTED_IN'");
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('meal_bookings', function (Blueprint $table) {
                $table->index('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_bookings', function (Blueprint $table) {
            $table->dropIndex(['id']);
            $table->dropColumn(['is_guest_pass', 'primary_student_id', 'guest_count']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status ENUM('booked', 'cancelled', 'consumed') NOT NULL DEFAULT 'booked'");
        }
    }
};
