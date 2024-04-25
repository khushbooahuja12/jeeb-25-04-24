<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('product_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fk_product_id')->unsigned()->nullable();
            $table->text('key_features_en')->nullable();
            $table->text('key_features_ar')->nullable();
            $table->text('disclamers_en')->nullable();
            $table->text('disclamers_ar')->nullable();
            $table->text('manufacturer_detail_en')->nullable();
            $table->text('manufacturer_detail_ar')->nullable();
            $table->text('marked_by_en')->nullable();
            $table->text('marked_by_ar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('product_detail');
    }

}
