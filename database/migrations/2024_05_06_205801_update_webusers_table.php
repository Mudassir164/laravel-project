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
        Schema::table('web_users', function (Blueprint $table) {
            $table->bigInteger('parent_id')->nullable()->after('role_id');
            $table->string('user_name')->nullable()->after('parent_id');
            $table->string('name')->nullable()->after('user_name');
            $table->string('business_name')->nullable()->after('password');
            $table->string('post_code')->nullable()->after('business_name');
            $table->tinyInteger('status')->default(1)->after('post_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_users', function (Blueprint $table) {
            $table->dropColumn('business_name');
            $table->dropColumn('post_code');
            $table->dropColumn('status');
            $table->dropColumn('user_name');
            $table->dropColumn('parent_id');
            $table->dropColumn('name');
        });
    }
};
