<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_connections', function (Blueprint $table) {
            $table->id();
            $table->string('connection_name');
            $table->string('tally_company_name');
            $table->string('tally_company_guid')->nullable();
            $table->string('host');
            $table->unsignedInteger('port')->default(9000);
            $table->string('protocol')->default('http');
            $table->string('api_format')->default('xml');
            $table->string('sync_agent_id')->nullable()->unique();
            $table->string('sync_agent_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_connections');
    }
};
