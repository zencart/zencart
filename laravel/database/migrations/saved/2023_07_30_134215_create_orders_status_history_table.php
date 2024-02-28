<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_status_history', function (Blueprint $table) {
            $table->increments('orders_status_history_id');
            $table->integer('orders_id')->default(0);
            $table->integer('orders_status_id')->default(0);
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->integer('customer_notified')->default(0);
            $table->text('comments')->nullable();
            $table->string('updated_by', 45)->default('');

            $table->index(['orders_id', 'orders_status_id'], 'idx_orders_id_status_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_status_history');
    }
}
