<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('incident_timelines', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // e.g. status change, note added, dispatched, etc.
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_timelines');
    }
};
