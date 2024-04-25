<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_user_id')->unsigned()->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->string('orderId')->nullable();
            $table->integer('fk_address_id')->unsigned()->nullable();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->integer('fk_coupon_id')->unsigned()->nullable();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->integer('fk_card_id')->unsigned()->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('orders');
    }

}
