<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('incident_logs', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id');
            $table->unsignedBigInteger('responder_id');
            $table->timestamp('resolved_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('responder_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('incident_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_logs');
    }
};
