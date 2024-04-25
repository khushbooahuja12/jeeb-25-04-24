<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('notes_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_notes_id')->unsigned()->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->integer('fk_product_id')->unsigned()->nullable();
            $table->integer('product_quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('notes_detail');
    }

}
