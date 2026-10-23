<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'booked'");

            DB::table('meal_bookings')->where('status', 'OPTED_IN')->update(['status' => 'booked']);
            DB::table('meal_bookings')->where('status', 'CANCELLED')->update(['status' => 'cancelled']);
            DB::table('meal_bookings')->where('status', 'CONSUMED')->update(['status' => 'consumed']);
            DB::table('meal_bookings')->where('status', 'PENDING_APPROVAL')->update(['status' => 'pending_approval']);
            DB::table('meal_bookings')->where('status', '')->update(['status' => 'cancelled']);

            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status ENUM('booked', 'cancelled', 'consumed', 'pending_approval') NOT NULL DEFAULT 'booked'");
        }

        DB::table('meal_bookings')
            ->where('status', 'booked')
            ->whereNull('confirmed_at')
            ->where('is_guest_pass', false)
            ->whereIn('menu_id', DB::table('menus')
                ->select('id')
                ->where('serve_at', '>', now()->addHours((int) config('mess.cutoff_hours'))))
            ->update(['status' => 'cancelled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'OPTED_IN'");

            DB::table('meal_bookings')->where('status', 'booked')->update(['status' => 'OPTED_IN']);
            DB::table('meal_bookings')->where('status', 'cancelled')->update(['status' => 'CANCELLED']);
            DB::table('meal_bookings')->where('status', 'consumed')->update(['status' => 'CONSUMED']);
            DB::table('meal_bookings')->where('status', 'pending_approval')->update(['status' => 'PENDING_APPROVAL']);

            DB::statement("ALTER TABLE meal_bookings MODIFY COLUMN status ENUM('OPTED_IN', 'CANCELLED', 'CONSUMED', 'PENDING_APPROVAL') NOT NULL DEFAULT 'OPTED_IN'");
        }
    }
};
