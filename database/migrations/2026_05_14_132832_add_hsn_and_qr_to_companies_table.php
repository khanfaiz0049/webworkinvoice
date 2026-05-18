<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('hsn_code')->nullable()->after('gst_number');
            $table->string('account_name')->nullable()->after('bank_name');
            $table->string('qr_code')->nullable()->after('upi_id');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'account_name', 'qr_code']);
        });
    }
};
