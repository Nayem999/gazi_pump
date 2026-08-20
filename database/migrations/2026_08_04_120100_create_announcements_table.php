<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('audience'); // all | role | territory | user (App\Enums\AnnouncementAudience)
            $table->string('audience_role')->nullable();
            $table->foreignId('audience_territory_id')->nullable()->constrained('territories')->nullOnDelete();
            $table->foreignId('audience_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('recipient_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
