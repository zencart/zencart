<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponGvQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_gv_queue', function (Blueprint $table) {
            $table->increments('unique_id');
            $table->integer('customer_id')->default(0);
            $table->integer('order_id')->default(0);
            $table->decimal('amount', 15, 4)->default(0.0000);
            $table->dateTime('date_created')->default('0001-01-01 00:00:00');
            $table->string('ipaddr', 45)->default('');
            $table->char('release_flag', 1)->default('N')->index('idx_release_flag_zen');

            $table->index(['customer_id', 'order_id'], 'idx_cust_id_order_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_gv_queue');
    }
}
