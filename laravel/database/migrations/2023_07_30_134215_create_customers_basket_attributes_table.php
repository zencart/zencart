<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersBasketAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_basket_attributes', function (Blueprint $table) {
            $table->increments('customers_basket_attributes_id');
            $table->integer('customers_id')->default(0);
            $table->string('products_id');
            $table->string('products_options_id', 64)->default('0');
            $table->integer('products_options_value_id')->default(0);
            $table->binary('products_options_value_text');
            $table->text('products_options_sort_order');

            $table->index(['customers_id', \Illuminate\Support\Facades\DB::raw('products_id(36)')], 'idx_cust_id_prod_id_zen');
            //$table->index(['customers_id', 'products_id`(36'], 'idx_cust_id_prod_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_basket_attributes');
    }
}
