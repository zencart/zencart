<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFilesUploadedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files_uploaded', function (Blueprint $table) {
            $table->increments('files_uploaded_id');
            $table->string('sesskey', 32)->nullable();
            $table->integer('customers_id')->nullable()->index('idx_customers_id_zen');
            $table->string('files_uploaded_name', 64)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files_uploaded');
    }
}
