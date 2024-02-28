<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersProductsDownloadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_products_download', function (Blueprint $table) {
            $table->increments('orders_products_download_id');
            $table->integer('orders_id')->default(0)->index('idx_orders_id_zen');
            $table->integer('orders_products_id')->default(0)->index('idx_orders_products_id_zen');
            $table->string('orders_products_filename')->default('');
            $table->integer('download_maxdays')->default(0);
            $table->integer('download_count')->default(0);
            $table->string('products_prid');
            $table->integer('products_attributes_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_products_download');
    }
}
