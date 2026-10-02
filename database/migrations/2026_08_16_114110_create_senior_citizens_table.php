<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Column order matters here: 2026_08_23_121951_add_psgc_codes_to_senior_citizens_table
     * inserts region/region_code/province_code/municipality_code/barangay_code
     * using ->after('address'), ->after('province'), ->after('municipality'),
     * ->after('barangay') — so those four base columns must already exist
     * exactly in this order before that migration runs.
     */
    public function up(): void
    {
        Schema::create('senior_citizens', function (Blueprint $table) {
            $table->id();

            // Nullable + unique: set only when this row was promoted from an
            // Application. Lets ApplicationController::promoteToSeniorCitizen()
            // use updateOrCreate(['application_id' => ...]) without duplicating.
            $table->foreignId('application_id')
                ->nullable()
                ->unique()
                ->constrained('applications')
                ->nullOnDelete();

            $table->string('list_name');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);

            // Base PSGC address fields (display names). The 2026_08_23
            // migration adds region + all the *_code columns after these.
            $table->string('address')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality');
            $table->string('barangay');

            $table->string('contact_number')->nullable();

            // Toggled off by the "Mark Deceased" action; index() filters to
            // is_active = true so deceased records drop off the master list.
            $table->boolean('is_active')->default(true);
            $table->timestamp('deceased_at')->nullable();

            $table->timestamps();

            $table->index('barangay');
            $table->index('is_active');
            $table->index('list_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senior_citizens');
    }
};