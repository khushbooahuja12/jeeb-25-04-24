<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('brand_name_en')->nullable();
            $table->string('brand_name_ar')->nullable();
            $table->integer('brand_image')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('brands');
    }

}
