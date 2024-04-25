<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('order_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_order_id')->unsigned()->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->integer('fk_product_id')->unsigned()->nullable();
            $table->decimal('product_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0)->comment('in percentage');
            $table->integer('product_quantity')->nullable();
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
        Schema::dropIfExists('order_products');
    }

}
