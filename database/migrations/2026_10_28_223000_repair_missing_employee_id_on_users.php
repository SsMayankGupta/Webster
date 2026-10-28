<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'employee_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        // employee_id is part of the users schema from an earlier migration.
    }
};
