<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            // Named explicitly, not derived from the current table name: the
            // index was created back when this table was still called
            // "customers" (`2026_08_03_050105_create_customers_table.php`),
            // and the later rename to "dealers" didn't rename the index
            // with it — MySQL happens to drop a single-column index
            // automatically when its column is dropped, but SQLite (used in
            // tests) doesn't, so it must be dropped explicitly by its real
            // name first.
            $table->dropIndex('customers_type_index');
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('type')->after('name');
            $table->index('type');
        });
    }
};
