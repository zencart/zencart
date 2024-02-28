<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_to_products', function (Blueprint $table) {
            $table->integer('media_id')->default(0);
            $table->integer('product_id')->default(0);

            $table->index(['media_id', 'product_id'], 'idx_media_product_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_to_products');
    }
}
