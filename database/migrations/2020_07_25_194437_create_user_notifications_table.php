<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_user_id')->unsigned()->nullable();
            $table->integer('related_id')->unsigned()->nullable();
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
        Schema::dropIfExists('user_notifications');
    }

}
