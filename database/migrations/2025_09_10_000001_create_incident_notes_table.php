<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('incident_notes', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id');
            $table->unsignedBigInteger('user_id');
            $table->text('note');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_notes');
    }
};
