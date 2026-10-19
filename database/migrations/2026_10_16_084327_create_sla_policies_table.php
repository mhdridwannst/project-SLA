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
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('priority'); // Menggunakan string yang dikontrol oleh TicketPriority Enum
            $table->unsignedInteger('first_response_time_minutes'); // Target balasan pertama teknisi
            $table->unsignedInteger('resolution_time_minutes');     // Target masalah selesai
            $table->timestamps();

            // Satu perusahaan hanya boleh punya 1 aturan untuk tiap level prioritas
            $table->unique(['tenant_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
