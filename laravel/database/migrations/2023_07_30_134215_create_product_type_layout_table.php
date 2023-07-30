<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductTypeLayoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_type_layout', function (Blueprint $table) {
            $table->increments('configuration_id');
            $table->text('configuration_title');
            $table->string('configuration_key', 180)->default('')->unique('unq_config_key_zen');
            $table->text('configuration_value');
            $table->text('configuration_description');
            $table->integer('product_type_id')->default(0);
            $table->integer('sort_order')->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->text('use_function')->nullable();
            $table->text('set_function')->nullable();

            $table->index(['configuration_key', \Illuminate\Support\Facades\DB::raw('configuration_value(10)')], 'idx_key_value_zen');
//            $table->index(['configuration_key', 'configuration_value`(10'], 'idx_key_value_zen');
            $table->index(['product_type_id', 'sort_order'], 'idx_type_id_sort_order_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type_layout');
    }
}
