<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverNotificationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('driver_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_driver_id')->unsigned()->nullable();
            $table->tinyInteger('notification_type')->nullable();
            $table->string('notification_title_en', 255)->nullable();
            $table->string('notification_title_ar', 255)->nullable();
            $table->text('notification_text_en')->nullable();
            $table->text('notification_text_ar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('driver_notifications');
    }

}
