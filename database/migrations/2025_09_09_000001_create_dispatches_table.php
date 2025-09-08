<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id'); // Firebase incident reference
            $table->unsignedBigInteger('responder_id'); // User table reference
            $table->string('status')->default('dispatched');
            $table->timestamps();

            $table->foreign('responder_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('incident_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispatches');
    }
};
