<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('senior_citizens', 'barangay_code')) {
            Schema::table('senior_citizens', function (Blueprint $table) {
                $table->string('barangay_code')
                    ->nullable()
                    ->after('barangay');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('senior_citizens', 'barangay_code')) {
            Schema::table('senior_citizens', function (Blueprint $table) {
                $table->dropColumn('barangay_code');
            });
        }
    }
};