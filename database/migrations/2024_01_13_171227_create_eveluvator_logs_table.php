<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEveluvatorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eveluvator_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('activity');
            $table->string('club_code');
            $table->string('comment')->nullable();
            $table->string('requested_range');
            $table->string('requested_points');
            $table->string('claimed_range');
            $table->string('claimed_points');
            $table->string('eveluvated_date');
            $table->integer('create_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eveluvator_logs');
    }
}
