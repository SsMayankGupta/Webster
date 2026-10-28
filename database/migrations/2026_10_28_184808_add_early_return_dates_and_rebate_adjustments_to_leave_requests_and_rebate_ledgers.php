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
        if (! Schema::hasColumn('leave_requests', 'requested_to_date')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->date('requested_to_date')->nullable()->after('to_date');
            });
        }

        DB::table('leave_requests')
            ->whereNull('requested_to_date')
            ->update(['requested_to_date' => DB::raw('to_date')]);

        $hasNonUniqueLeaveRequestIndex = collect(Schema::getIndexes('rebate_ledgers'))
            ->contains(fn (array $index): bool => $index['columns'] === ['leave_request_id'] && ! $index['unique']);

        if (! $hasNonUniqueLeaveRequestIndex) {
            Schema::table('rebate_ledgers', function (Blueprint $table) {
                $table->index('leave_request_id', 'rebate_ledgers_leave_request_id_index');
            });
        }

        if (Schema::hasIndex('rebate_ledgers', 'rebate_ledgers_leave_request_id_unique')) {
            Schema::table('rebate_ledgers', function (Blueprint $table) {
                $table->dropUnique('rebate_ledgers_leave_request_id_unique');
            });
        }

        Schema::table('rebate_ledgers', function (Blueprint $table) {
            $table->bigInteger('amount_paise')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasMultipleEntries = DB::table('rebate_ledgers')
            ->select('leave_request_id')
            ->groupBy('leave_request_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        $hasNegativeEntries = DB::table('rebate_ledgers')
            ->where('amount_paise', '<', 0)
            ->exists();

        if ($hasMultipleEntries || $hasNegativeEntries) {
            throw new RuntimeException(
                'Cannot roll back early-return rebate adjustments while signed or multiple rebate entries exist.',
            );
        }

        Schema::table('rebate_ledgers', function (Blueprint $table) {
            $table->unsignedBigInteger('amount_paise')->nullable()->change();
            if (! Schema::hasIndex('rebate_ledgers', 'rebate_ledgers_leave_request_id_unique')) {
                $table->unique('leave_request_id');
            }
            if (Schema::hasIndex('rebate_ledgers', 'rebate_ledgers_leave_request_id_index')) {
                $table->dropIndex('rebate_ledgers_leave_request_id_index');
            }
        });
    }
};
