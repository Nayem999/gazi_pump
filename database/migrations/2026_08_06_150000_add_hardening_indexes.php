<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 19 (Hardening) index review: these five columns are filtered/sorted
 * on independently of the composite indexes their tables already had (e.g.
 * `visits` only had `(user_id, check_in_at)`, which doesn't help an admin
 * report that filters by date range across all users), or weren't indexed
 * at all despite being a common list-page filter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->index(['audience', 'created_at']);
        });

        Schema::table('achievements', function (Blueprint $table): void {
            $table->index('grade');
        });

        Schema::table('visits', function (Blueprint $table): void {
            $table->index('check_in_at');
        });

        Schema::table('sales_entries', function (Blueprint $table): void {
            $table->index('sale_date');
        });

        Schema::table('collection_entries', function (Blueprint $table): void {
            $table->index('collection_date');
        });

        Schema::table('visit_plans', function (Blueprint $table): void {
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropIndex(['audience', 'created_at']);
        });

        Schema::table('achievements', function (Blueprint $table): void {
            $table->dropIndex(['grade']);
        });

        Schema::table('visits', function (Blueprint $table): void {
            $table->dropIndex(['check_in_at']);
        });

        Schema::table('sales_entries', function (Blueprint $table): void {
            $table->dropIndex(['sale_date']);
        });

        Schema::table('collection_entries', function (Blueprint $table): void {
            $table->dropIndex(['collection_date']);
        });

        Schema::table('visit_plans', function (Blueprint $table): void {
            $table->dropIndex(['planned_date']);
        });
    }
};
