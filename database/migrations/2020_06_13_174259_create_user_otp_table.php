<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOtpTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_otp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('user_type')->nullable()->comment('1:user,2:driver');
            $table->integer('user_id')->unsigned()->nullable()->comment('user_id or driver_id');
            $table->integer('otp_number')->nullable()->comment('4 digit random number');
            $table->timestamp('expiry_date')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1:for register,2:for forgot pw');
            $table->tinyInteger('is_used')->comment('1:yes,0:no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_otp');
    }

}
