<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalTestingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_testing', function (Blueprint $table) {
            $table->increments('paypal_ipn_id');
            $table->unsignedInteger('order_id')->default(0)->index('idx_order_id_zen');
            $table->string('custom')->default('');
            $table->string('txn_type', 40)->default('');
            $table->string('module_name', 40)->default('');
            $table->string('module_mode', 40)->default('');
            $table->string('reason_code', 40)->nullable();
            $table->string('payment_type', 40)->default('');
            $table->string('payment_status', 32)->default('');
            $table->string('pending_reason', 32)->nullable();
            $table->string('invoice', 128)->nullable();
            $table->char('mc_currency', 3)->default('');
            $table->string('first_name', 32)->default('');
            $table->string('last_name', 32)->default('');
            $table->string('payer_business_name', 128)->nullable();
            $table->string('address_name', 64)->nullable();
            $table->string('address_street', 254)->nullable();
            $table->string('address_city', 120)->nullable();
            $table->string('address_state', 120)->nullable();
            $table->string('address_zip', 10)->nullable();
            $table->string('address_country', 64)->nullable();
            $table->string('address_status', 11)->nullable();
            $table->string('payer_email', 128)->default('');
            $table->string('payer_id', 32)->default('');
            $table->string('payer_status', 10)->default('');
            $table->dateTime('payment_date')->default('0001-01-01 00:00:00');
            $table->string('business', 128)->default('');
            $table->string('receiver_email', 128)->default('');
            $table->string('receiver_id', 32)->default('');
            $table->string('txn_id', 20)->default('');
            $table->string('parent_txn_id', 20)->nullable();
            $table->unsignedTinyInteger('num_cart_items')->default(1);
            $table->decimal('mc_gross', 7, 2)->default(0.00);
            $table->decimal('mc_fee', 7, 2)->default(0.00);
            $table->decimal('payment_gross', 7, 2)->nullable();
            $table->decimal('payment_fee', 7, 2)->nullable();
            $table->decimal('settle_amount', 7, 2)->nullable();
            $table->char('settle_currency', 3)->nullable();
            $table->decimal('exchange_rate', 4, 2)->nullable();
            $table->decimal('notify_version', 2, 1)->default(0.0);
            $table->string('verify_sign', 128)->default('');
            $table->dateTime('last_modified')->default('0001-01-01 00:00:00');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->text('memo')->nullable();

            $table->index(['paypal_ipn_id']);
            $table->dropPrimary('paypal_ipn_id');
            $table->primary(['paypal_ipn_id', 'txn_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_testing');
    }
}
