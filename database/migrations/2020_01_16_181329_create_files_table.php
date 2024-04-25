<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_path')->nullable()->comment('path of the file except the ip');
            $table->string('file_name')->nullable()->comment('name of the file with extension');
            $table->string('file_ext')->nullable()->comment('file extension like .jpg,.png etc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('files');
    }

}
