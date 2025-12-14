<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('fixed_percent', 5, 2)->nullable()->after('active')->comment('Fixed commission percentage');
            $table->boolean('bonus')->default(false)->after('fixed_percent')->comment('Bonus commission enabled');
            $table->boolean('miscellaneous_commission')->default(false)->after('bonus')->comment('Miscellaneous commission enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['fixed_percent', 'bonus', 'miscellaneous_commission']);
        });
    }
};
