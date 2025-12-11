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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('card'); // card, bank_account, etc.
            $table->string('card_type')->nullable(); // visa, mastercard, amex, etc.
            $table->string('last_four', 4); // Last 4 digits
            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();
            $table->string('holder_name')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('provider')->nullable(); // stripe, paypal, etc.
            $table->string('provider_id')->nullable(); // External provider ID
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
