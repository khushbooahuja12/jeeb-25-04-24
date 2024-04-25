<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWishlistTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_wishlist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_user_id')->unsigned()->nullable();
            $table->integer('fk_product_id')->unsigned()->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_wishlist');
    }

}
