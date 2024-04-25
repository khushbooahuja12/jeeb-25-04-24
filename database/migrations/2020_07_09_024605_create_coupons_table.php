<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->nullable()->comment('1:percent,2:amount');
            $table->tinyInteger('offer_type')->nullable()->comment('1:on minimum purchase,2:on category,3:on brand');
            $table->decimal('min_amount', 10, 2)->default(0)->nullable();
            $table->integer('fk_category_id')->unsigned()->nullable();
            $table->integer('fk_brand_id')->unsigned()->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('discount', 10, 2)->default(0)->comment('in percent or amount');
            $table->date('expiry_date')->nullable();
            $table->integer('uses_limit')->nullable();
            $table->tinyInteger('status')->nullable()->comment('1:active,0:inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('coupons');
    }

}
