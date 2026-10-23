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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared('
            CREATE TRIGGER prevent_consumption_ledger_update
            BEFORE UPDATE ON meal_consumptions
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: UPDATE not allowed on meal_consumptions";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_consumption_ledger_delete
            BEFORE DELETE ON meal_consumptions
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: DELETE not allowed on meal_consumptions";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_rebate_ledger_update
            BEFORE UPDATE ON rebate_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: UPDATE not allowed on rebate_ledgers";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_rebate_ledger_delete
            BEFORE DELETE ON rebate_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: DELETE not allowed on rebate_ledgers";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_headcount_ledger_update
            BEFORE UPDATE ON headcount_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: UPDATE not allowed on headcount_ledgers";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_headcount_ledger_delete
            BEFORE DELETE ON headcount_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Ledgers are append-only: DELETE not allowed on headcount_ledgers";
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS prevent_consumption_ledger_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_consumption_ledger_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_rebate_ledger_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_rebate_ledger_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_headcount_ledger_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_headcount_ledger_delete');
    }
};
