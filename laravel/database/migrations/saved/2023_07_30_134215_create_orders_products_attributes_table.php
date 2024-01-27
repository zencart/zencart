<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersProductsAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_products_attributes', function (Blueprint $table) {
            $table->increments('orders_products_attributes_id');
            $table->integer('orders_id')->default(0);
            $table->integer('orders_products_id')->default(0);
            $table->string('products_options', 32)->default('');
            $table->text('products_options_values');
            $table->decimal('options_values_price', 15, 4)->default(0.0000);
            $table->char('price_prefix', 1)->default('');
            $table->boolean('product_attribute_is_free')->default(0);
            $table->float('products_attributes_weight')->default(0);
            $table->char('products_attributes_weight_prefix', 1)->default('');
            $table->boolean('attributes_discounted')->default(1);
            $table->boolean('attributes_price_base_included')->default(1);
            $table->decimal('attributes_price_onetime', 15, 4)->default(0.0000);
            $table->decimal('attributes_price_factor', 15, 4)->default(0.0000);
            $table->decimal('attributes_price_factor_offset', 15, 4)->default(0.0000);
            $table->decimal('attributes_price_factor_onetime', 15, 4)->default(0.0000);
            $table->decimal('attributes_price_factor_onetime_offset', 15, 4)->default(0.0000);
            $table->text('attributes_qty_prices')->nullable();
            $table->text('attributes_qty_prices_onetime')->nullable();
            $table->decimal('attributes_price_words', 15, 4)->default(0.0000);
            $table->integer('attributes_price_words_free')->default(0);
            $table->decimal('attributes_price_letters', 15, 4)->default(0.0000);
            $table->integer('attributes_price_letters_free')->default(0);
            $table->integer('products_options_id')->default(0);
            $table->integer('products_options_values_id')->default(0);
            $table->string('products_prid');

            $table->index(['orders_id', 'orders_products_id'], 'idx_orders_id_prod_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_products_attributes');
    }
}
