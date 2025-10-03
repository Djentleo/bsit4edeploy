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
        Schema::create('incident_logs', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id')->unique();
            $table->string('type')->nullable();
            $table->string('location')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('reporter_id')->nullable();
            $table->string('department')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('source')->nullable();
            $table->text('incident_description')->nullable();
            $table->string('priority')->nullable();
            $table->string('severity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_logs');
    }
};
