<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subsidy_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('senior_citizen_id')
                ->constrained('senior_citizens')
                ->cascadeOnDelete();

            $table->foreignId('subsidy_id')
                ->constrained('subsidies')
                ->cascadeOnDelete();

            $table->string('application_number')->unique();

            $table->date('application_date');

            $table->enum('status', [
                'pending',
                'for_verification',
                'approved',
                'rejected'
            ])->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subsidy_applications');
    }
};