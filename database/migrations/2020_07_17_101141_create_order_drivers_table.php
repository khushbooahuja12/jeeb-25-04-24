<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDriversTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_drivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_order_id')->unsigned()->nullable();
            $table->integer('fk_driver_id')->unsigned()->nullable();
            $table->tinyInteger('status')->nullable();
            $table->decimal('driver_earning', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('order_drivers');
    }

}
