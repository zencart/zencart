<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_attributes', function (Blueprint $table) {
            $table->increments('products_attributes_id');
            $table->integer('products_id')->default(0);
            $table->integer('options_id')->default(0);
            $table->integer('options_values_id')->default(0);
            $table->decimal('options_values_price', 15, 4)->default(0.0000);
            $table->char('price_prefix', 1)->default('');
            $table->integer('products_options_sort_order')->default(0)->index('idx_opt_sort_order_zen');
            $table->boolean('product_attribute_is_free')->default(0);
            $table->float('products_attributes_weight')->default(0);
            $table->char('products_attributes_weight_prefix', 1)->default('');
            $table->boolean('attributes_display_only')->default(0);
            $table->boolean('attributes_default')->default(0);
            $table->boolean('attributes_discounted')->default(1);
            $table->string('attributes_image')->nullable();
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
            $table->boolean('attributes_required')->default(0);

            $table->index(['products_id', 'options_id', 'options_values_id'], 'idx_id_options_id_values_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_attributes');
    }
}
