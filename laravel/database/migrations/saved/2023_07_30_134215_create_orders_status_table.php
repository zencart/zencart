<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_status', function (Blueprint $table) {
            $table->integer('orders_status_id')->default(0);
            $table->integer('language_id')->default(1);
            $table->string('orders_status_name', 32)->default('')->index('idx_orders_status_name_zen');
            $table->integer('sort_order')->default(0);

            $table->primary(['orders_status_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_status');
    }
}
