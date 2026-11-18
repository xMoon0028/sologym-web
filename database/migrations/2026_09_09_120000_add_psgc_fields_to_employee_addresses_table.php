<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('employee_addresses', function (Blueprint $table) {
            $table->string('region')->nullable()->after('employee_id');
            $table->string('province')->nullable()->after('region');
            $table->string('municipality_city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('municipality_city');
        });
    }

    public function down(): void {
        Schema::table('employee_addresses', function (Blueprint $table) {
            $table->dropColumn(['region', 'province', 'municipality_city', 'barangay']);
        });
    }
};
