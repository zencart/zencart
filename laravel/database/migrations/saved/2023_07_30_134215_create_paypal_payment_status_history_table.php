<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalPaymentStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_payment_status_history', function (Blueprint $table) {
            $table->integer('payment_status_history_id');
            $table->integer('paypal_ipn_id')->default(0)->index('idx_paypal_ipn_id_zen');
            $table->string('txn_id', 64)->default('');
            $table->string('parent_txn_id', 64)->default('');
            $table->string('payment_status', 17)->default('');
            $table->string('pending_reason', 32)->nullable();
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_payment_status_history');
    }
}
