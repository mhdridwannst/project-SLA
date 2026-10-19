<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('ticket_number')->unique();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sla_policy_id')->constrained('sla_policies');
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('open')->index();

            // Pelacakan SLA Respon Pertama & Penyelesaian
            $table->timestamp('first_response_deadline_at');
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('sla_deadline_at');
            $table->timestamp('resolved_at')->nullable();

            // Mekanisme SLA Pause (Saat menunggu balasan customer)
            $table->timestamp('sla_paused_at')->nullable();
            $table->unsignedInteger('total_paused_minutes')->default(0);

            $table->boolean('is_sla_breached')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
