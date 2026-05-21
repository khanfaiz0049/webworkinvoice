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
        // 1. Add performa_invoice_starting_number to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('performa_invoice_starting_number')->default(1)->after('invoice_starting_number');
        });

        // 2. Create performa_invoices table
        Schema::create('performa_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number'); // Named invoice_number to keep code replication identical
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // draft, pending, paid, partial, overdue, cancelled
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->boolean('gst_enabled')->default(true);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('igst', 15, 2)->default(0);
            $table->decimal('total_gst', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->date('renewal_date')->nullable();
            $table->string('renewal_text')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'invoice_number'], 'performa_invoices_company_number_unique');
        });

        // 3. Create performa_invoice_items table
        Schema::create('performa_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performa_invoice_id')->constrained('performa_invoices')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('hsn_sac')->nullable();
            $table->decimal('quantity', 15, 2)->default(1);
            $table->string('unit')->default('Nos');
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('igst', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performa_invoice_items');
        Schema::dropIfExists('performa_invoices');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('performa_invoice_starting_number');
        });
    }
};
