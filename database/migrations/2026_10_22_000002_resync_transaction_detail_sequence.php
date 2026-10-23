<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('transaction_details', 'id'),
                COALESCE(MAX(id), 1),
                MAX(id) IS NOT NULL
            )
            FROM transaction_details
            SQL);
    }

    public function down(): void
    {
        // Sequence values cannot be safely rolled back.
    }
};
