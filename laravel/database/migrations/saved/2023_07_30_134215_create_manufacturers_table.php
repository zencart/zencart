<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManufacturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->increments('manufacturers_id');
            $table->string('manufacturers_name', 32)->default('')->index('idx_mfg_name_zen');
            $table->string('manufacturers_image')->nullable();
            $table->dateTime('date_added')->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->boolean('featured')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturers');
    }
}
