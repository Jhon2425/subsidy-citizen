<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('senior_citizens', function (Blueprint $table) {
            $table->string('region')->nullable()->after('address');
            $table->string('region_code', 12)->nullable()->after('region');
            $table->string('province_code', 12)->nullable()->after('province');
            $table->string('municipality_code', 12)->nullable()->after('municipality');
            $table->string('barangay_code', 12)->nullable()->after('barangay');

            $table->index('barangay_code');
        });
    }

    public function down(): void
    {
        Schema::table('senior_citizens', function (Blueprint $table) {
            $table->dropIndex(['barangay_code']);

            $table->dropColumn([
                'region',
                'region_code',
                'province_code',
                'municipality_code',
                'barangay_code',
            ]);
        });
    }
};