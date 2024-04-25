<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSupportTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('customer_support', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_user_id')->unsigned()->nullable();
            $table->string('subject')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('screenshots')->nullable()->comment('comma separated ids of images');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('customer_support');
    }

}
