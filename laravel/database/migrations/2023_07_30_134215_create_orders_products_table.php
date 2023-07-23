<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_products', function (Blueprint $table) {
            $table->increments('orders_products_id');
            $table->integer('orders_id')->default(0);
            $table->integer('products_id')->default(0);
            $table->string('products_model', 32)->nullable();
            $table->string('products_name', 191)->default('');
            $table->decimal('products_price', 15, 4)->default(0.0000);
            $table->decimal('final_price', 15, 4)->default(0.0000);
            $table->decimal('products_tax', 7, 4)->default(0.0000);
            $table->float('products_quantity')->default(0);
            $table->decimal('onetime_charges', 15, 4)->default(0.0000);
            $table->boolean('products_priced_by_attribute')->default(0);
            $table->boolean('product_is_free')->default(0);
            $table->boolean('products_discount_type')->default(0);
            $table->boolean('products_discount_type_from')->default(0);
            $table->string('products_prid');
            $table->float('products_weight')->nullable();
            $table->tinyInteger('products_virtual')->nullable();
            $table->tinyInteger('product_is_always_free_shipping')->nullable();
            $table->float('products_quantity_order_min')->nullable();
            $table->float('products_quantity_order_units')->nullable();
            $table->float('products_quantity_order_max')->nullable();
            $table->tinyInteger('products_quantity_mixed')->nullable();
            $table->tinyInteger('products_mixed_discount_quantity')->nullable();

            $table->index(['orders_id', 'products_id'], 'idx_orders_id_prod_id_zen');
            $table->index(['products_id', 'orders_id'], 'idx_prod_id_orders_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_products');
    }
}
