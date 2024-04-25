<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorOrdersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('vendor_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sub_orderId')->nullable();
            $table->integer('fk_order_id')->unsigned()->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->tinyInteger('pickup')->default(0)->nullable()->comment('1:yes,0:no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('vendor_orders');
    }

}
