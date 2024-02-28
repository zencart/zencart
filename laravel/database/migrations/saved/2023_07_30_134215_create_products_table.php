<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('products_id');
            $table->integer('products_type')->default(1);
            $table->float('products_quantity')->default(0);
            $table->string('products_model', 32)->nullable()->index('idx_products_model_zen');
            $table->string('products_image')->nullable();
            $table->decimal('products_price', 15, 4)->default(0.0000);
            $table->boolean('products_virtual')->default(0);
            $table->dateTime('products_date_added')->default('0001-01-01 00:00:00')->index('idx_products_date_added_zen');
            $table->dateTime('products_last_modified')->nullable();
            $table->dateTime('products_date_available')->nullable()->index('idx_products_date_available_zen');
            $table->float('products_weight')->default(0);
            $table->boolean('products_status')->default(0)->index('idx_products_status_zen');
            $table->integer('products_tax_class_id')->default(0);
            $table->integer('manufacturers_id')->nullable()->index('idx_manufacturers_id_zen');
            $table->float('products_ordered')->default(0)->index('idx_products_ordered_zen');
            $table->float('products_quantity_order_min')->default(1);
            $table->float('products_quantity_order_units')->default(1);
            $table->boolean('products_priced_by_attribute')->default(0);
            $table->boolean('product_is_free')->default(0);
            $table->boolean('product_is_call')->default(0);
            $table->boolean('products_quantity_mixed')->default(0);
            $table->boolean('product_is_always_free_shipping')->default(0);
            $table->boolean('products_qty_box_status')->default(1);
            $table->float('products_quantity_order_max')->default(0);
            $table->integer('products_sort_order')->default(0)->index('idx_products_sort_order_zen');
            $table->boolean('products_discount_type')->default(0);
            $table->boolean('products_discount_type_from')->default(0);
            $table->decimal('products_price_sorter', 15, 4)->default(0.0000)->index('idx_products_price_sorter_zen');
            $table->integer('master_categories_id')->default(0)->index('idx_master_categories_id_zen');
            $table->boolean('products_mixed_discount_quantity')->default(1);
            $table->boolean('metatags_title_status')->default(0);
            $table->boolean('metatags_products_name_status')->default(0);
            $table->boolean('metatags_model_status')->default(0);
            $table->boolean('metatags_price_status')->default(0);
            $table->boolean('metatags_title_tagline_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
