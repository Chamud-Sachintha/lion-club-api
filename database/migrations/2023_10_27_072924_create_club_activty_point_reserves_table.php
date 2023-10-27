<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubActivtyPointReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_activty_point_reserves', function (Blueprint $table) {
            $table->id();
            $table->string('club_activity_code');
            $table->string('club_code');
            $table->integer('points');
            $table->integer('time');
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
        Schema::dropIfExists('club_activty_point_reserves');
    }
}
