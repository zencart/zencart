<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsOptionsValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_options_values', function (Blueprint $table) {
            $table->integer('products_options_values_id')->default(0);
            $table->integer('language_id')->default(1);
            $table->string('products_options_values_name', 64)->default('')->index('idx_products_options_values_name_zen');
            $table->integer('products_options_values_sort_order')->default(0)->index('idx_products_options_values_sort_order_zen');

            $table->primary(['products_options_values_id', 'language_id'], 'idx_products_options_values_id_language_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_options_values');
    }
}
