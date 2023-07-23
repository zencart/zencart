<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponEmailTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_email_track', function (Blueprint $table) {
            $table->increments('unique_id');
            $table->integer('coupon_id')->default(0)->index('idx_coupon_id_zen');
            $table->integer('customer_id_sent')->default(0);
            $table->string('sent_firstname', 32)->nullable();
            $table->string('sent_lastname', 32)->nullable();
            $table->string('emailed_to', 96)->nullable();
            $table->dateTime('date_sent')->default('0001-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_email_track');
    }
}
