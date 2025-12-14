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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // create, update, delete, view, etc.
            $table->string('module'); // Users, Packages, Payments, etc.
            $table->string('model_type')->nullable(); // App\Models\User, App\Models\Package, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->string('description')->nullable(); // Human-readable description
            $table->text('old_values')->nullable(); // JSON of old values (for updates)
            $table->text('new_values')->nullable(); // JSON of new values
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('route')->nullable(); // Route name or URL
            $table->text('request_data')->nullable(); // JSON of request data
            $table->timestamps();

            // Indexes for better query performance
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
