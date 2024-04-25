<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDeviceDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_device_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fk_user_id')->unsigned()->nullable();
            $table->tinyInteger('device_type')->default(0)->comment('1:iOS,2:Android');
            $table->string('device_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_device_detail');
    }

}
