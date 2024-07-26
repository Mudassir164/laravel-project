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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('identifier');
            $table->string('route_name');
            $table->bigInteger('ref_id')->default(0);
            $table->bigInteger('sender_id')->default(0);
            $table->bigInteger('receiver_id');
            $table->string('replacers');
            $table->tinyInteger('read')->default(0);
            $table->tinyInteger('is_push')->default(1);
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
        Schema::dropIfExists('notifications');
    }
};
