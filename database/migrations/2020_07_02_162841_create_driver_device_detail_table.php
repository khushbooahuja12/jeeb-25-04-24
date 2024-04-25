<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverDeviceDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('driver_device_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fk_driver_id')->unsigned()->nullable();
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
        Schema::dropIfExists('driver_device_detail');
    }

}
