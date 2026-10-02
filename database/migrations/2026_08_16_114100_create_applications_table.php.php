<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subsidy_id')
                ->constrained('subsidies')
                ->restrictOnDelete();

            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->default('');
            $table->string('name_extension', 10)->default('');

            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);

            $table->string('address')->default('');

            $table->string('region');
            $table->string('region_code', 12);

            // Empty for regions with no province level (e.g. NCR)
            $table->string('province')->default('');
            $table->string('province_code', 12)->default('');

            $table->string('municipality');
            $table->string('municipality_code', 12);

            $table->string('barangay');
            $table->string('barangay_code', 12);

            $table->string('contact_number', 20)->default('');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Filled for pending / rejected, '' when approved
            $table->string('reason', 500)->default('');

            $table->timestamps();

            $table->index('status');
            $table->index('last_name');
            $table->index('first_name');
            $table->index('barangay_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};