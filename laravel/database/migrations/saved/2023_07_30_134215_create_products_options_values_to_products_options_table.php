<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsOptionsValuesToProductsOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_options_values_to_products_options', function (Blueprint $table) {
            $table->integer('products_options_values_to_products_options_id')->primary('idx_primary');
            $table->integer('products_options_id')->default(0)->index('idx_products_options_id_zen');
            $table->integer('products_options_values_id')->default(0)->index('idx_products_options_values_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_options_values_to_products_options');
    }
}
