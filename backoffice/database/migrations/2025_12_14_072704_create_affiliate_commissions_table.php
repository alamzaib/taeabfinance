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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade'); // User who referred
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade'); // User who was referred
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null'); // Payment that generated commission
            $table->decimal('commission_amount', 10, 2);
            $table->string('commission_type')->default('percentage'); // percentage or fixed
            $table->decimal('commission_rate', 5, 2)->nullable(); // Percentage rate used
            $table->string('status')->default('pending'); // pending, approved, paid, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('payment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
