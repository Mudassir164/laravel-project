<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_submission_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('game_submission_id');
            $table->bigInteger('game_question_id');
            $table->bigInteger('game_question_option_id')->nullable();
            $table->bigInteger('time_spent')->nullable();
            $table->boolean('correct_answer')->default(0);
            $table->text('answer')->nullable();
            // $table->string('photo')->nullable();
            // $table->string('video')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_submission_details');
    }
};
