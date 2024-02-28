<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->increments('type_id');
            $table->string('type_name')->default('');
            $table->string('type_handler')->default('');
            $table->integer('type_master_type')->default(1)->index('idx_type_master_type_zen');
            $table->char('allow_add_to_cart', 1)->default('Y');
            $table->string('default_image')->default('');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->dateTime('last_modified')->default('0001-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_types');
    }
}
