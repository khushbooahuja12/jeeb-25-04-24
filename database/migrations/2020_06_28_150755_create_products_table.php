<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->nullable();
            $table->integer('fk_vendor_id')->unsigned()->nullable();
            $table->integer('fk_category_id')->unsigned()->nullable();
            $table->integer('fk_brand_id')->unsigned()->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name_en')->nullable();
            $table->string('product_name_ar')->nullable();
            $table->integer('product_image')->unsigned()->nullable();
            $table->decimal('product_price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0)->comment('in percentage');
            $table->integer('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->tinyInteger('essential')->default()->comment('1:yes,0:no');
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
        Schema::dropIfExists('products');
    }

}
