<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponRedeemTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_redeem_track', function (Blueprint $table) {
            $table->increments('unique_id');
            $table->integer('coupon_id')->default(0)->index('idx_coupon_id_zen');
            $table->integer('customer_id')->default(0);
            $table->dateTime('redeem_date')->default('0001-01-01 00:00:00');
            $table->string('redeem_ip', 45)->default('');
            $table->integer('order_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_redeem_track');
    }
}
