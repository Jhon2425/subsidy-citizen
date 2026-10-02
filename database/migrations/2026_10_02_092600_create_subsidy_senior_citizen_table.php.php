<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subsidy_senior_citizen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('senior_citizen_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // prevents duplicate enlisting
            $table->unique(['subsidy_id', 'senior_citizen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subsidy_senior_citizen');
    }
};