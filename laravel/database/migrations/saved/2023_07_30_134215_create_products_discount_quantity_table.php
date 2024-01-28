<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsDiscountQuantityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_discount_quantity', function (Blueprint $table) {
            $table->integer('discount_id')->default(0);
            $table->integer('products_id')->default(0);
            $table->float('discount_qty')->default(0);
            $table->decimal('discount_price', 15, 4)->default(0.0000);

            $table->index(['products_id', 'discount_qty'], 'idx_id_qty_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_discount_quantity');
    }
}
