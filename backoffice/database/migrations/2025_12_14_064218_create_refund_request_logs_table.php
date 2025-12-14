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
        Schema::create('refund_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // create, update, delete, approve, reject
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('refund_request_id');
            $table->index('changed_by');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_request_logs');
    }
};
