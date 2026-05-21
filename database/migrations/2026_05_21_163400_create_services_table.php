<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed the default services from the old constant
        $defaults = [
            'Domain',
            'Hosting',
            'AMC Yearly',
            'AMC Monthly',
            'SEO Monthly',
            'Digital Marketing Monthly',
            'GSUIT Yearly',
            'ZOHO Yearly',
        ];

        foreach ($defaults as $name) {
            DB::table('services')->insert([
                'name'       => $name,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
