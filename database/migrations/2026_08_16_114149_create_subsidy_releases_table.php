<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subsidy_releases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('senior_citizen_id')->constrained('senior_citizens')->restrictOnDelete();
            $table->foreignId('subsidy_id')->constrained('subsidies')->restrictOnDelete();

            $table->string('reference_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->date('release_date');

            $table->enum('status', ['pending', 'released', 'cancelled'])->default('pending');

            $table->string('released_by')->default('');
            $table->string('remarks', 500)->default('');

            $table->timestamps();

            $table->unique(['senior_citizen_id', 'subsidy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subsidy_releases');
    }
};