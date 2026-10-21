<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'dietary_tags')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('dietary_tags')->nullable()->after('dietary_pref');
            });
        }

        if (! Schema::hasColumn('menus', 'items')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->json('items')->nullable()->after('recipe_text');
            });
        }

        if (! Schema::hasColumn('meal_bookings', 'assigned_lane')) {
            Schema::table('meal_bookings', function (Blueprint $table) {
                $table->string('dietary_pref')->nullable()->after('status');
                $table->string('assigned_lane')->nullable()->after('dietary_pref');
                $table->timestamp('confirmed_at')->nullable()->after('assigned_lane');
            });
        }

        Schema::table('meal_bookings', function (Blueprint $table) {
            $table->unique(['student_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::table('meal_bookings', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'menu_id']);
        });

        if (Schema::hasColumn('meal_bookings', 'assigned_lane')) {
            Schema::table('meal_bookings', function (Blueprint $table) {
                $table->dropColumn(['dietary_pref', 'assigned_lane', 'confirmed_at']);
            });
        }

        if (Schema::hasColumn('menus', 'items')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }

        if (Schema::hasColumn('users', 'dietary_tags')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('dietary_tags');
            });
        }
    }
};
