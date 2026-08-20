<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A customer's own request for a sales visit, submitted from their portal
 * dashboard — deliberately requires a logged-in customer_account_id (unlike
 * Inquiry, which also accepts anonymous submissions), since a visit request
 * needs a real address and a way to follow up. Distinct from VisitPlan/Visit
 * (Module 7), which are the internal Sales Executive-side workflow — this is
 * the customer-initiated request that (in a later phase) could be reconciled
 * into a VisitPlan by an admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_account_id')->constrained('customer_accounts')->cascadeOnDelete();
            $table->date('preferred_date');
            $table->text('address');
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending | scheduled | completed | cancelled (App\Enums\VisitRequestStatus)

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_requests');
    }
};
