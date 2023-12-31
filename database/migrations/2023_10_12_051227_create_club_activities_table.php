<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code');
            $table->string('club_code');
            $table->integer('type');
            $table->integer('create_time');
            $table->integer('status');
            $table->string('creator');
            $table->string('ext_value');
            $table->integer('date_of_activity');
            $table->string('comment')->nullable();
            $table->string('aditional_info')->nullable();
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
        Schema::dropIfExists('club_activities');
    }
}
