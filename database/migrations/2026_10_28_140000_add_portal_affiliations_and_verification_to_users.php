<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('student')->change();
            $table->string('phone')->nullable()->unique();
            $table->string('employee_id')->nullable()->unique();
            $table->date('registered_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('account_approved_at')->nullable();
            $table->foreignId('warden_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('users')
            ->whereIn('role', ['student', 'contractor', 'admin'])
            ->whereNull('account_approved_at')
            ->update(['account_approved_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('warden_id');
            $table->dropConstrainedForeignId('contractor_id');
            $table->dropUnique(['phone']);
            $table->dropUnique(['employee_id']);
            $table->dropColumn([
                'phone',
                'employee_id',
                'registered_at',
                'phone_verified_at',
                'account_approved_at',
            ]);
        });
    }
};
