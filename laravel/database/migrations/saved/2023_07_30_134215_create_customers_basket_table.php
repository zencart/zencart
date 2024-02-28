<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersBasketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_basket', function (Blueprint $table) {
            $table->increments('customers_basket_id');
            $table->integer('customers_id')->default(0)->index('idx_customers_id_zen');
            $table->string('products_id');
            $table->float('customers_basket_quantity')->default(0);
            $table->string('customers_basket_date_added', 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_basket');
    }
}
