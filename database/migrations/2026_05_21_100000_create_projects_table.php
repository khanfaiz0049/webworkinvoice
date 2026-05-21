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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');                          // Project Name
            $table->json('services')->nullable();            // Multi-select services (Domain, Hosting, etc.)
            $table->decimal('amount', 12, 2)->default(0);    // Project amount
            $table->date('start_date');                       // Project start date
            $table->date('renewal_date');                     // Next renewal date
            $table->string('renewal_period');                 // 1_month, 3_months, 6_months, yearly
            $table->string('status')->default('open');        // open | closed
            $table->text('notes')->nullable();                // Optional notes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
