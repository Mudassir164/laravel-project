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
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('web_user_id');
            $table->bigInteger('game_id');
            $table->string('image')->nullable();
            $table->string('title');
            $table->string('sponsor_name');
            $table->integer('amount');
            $table->text('message');
            $table->tinyInteger('status')->default(0);
            $table->string('expiry_date');
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
        Schema::dropIfExists('sponsorships');
    }
};
