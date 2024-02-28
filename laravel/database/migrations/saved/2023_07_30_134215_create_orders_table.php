<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('orders_id');
            $table->integer('customers_id')->default(0);
            $table->string('customers_name', 64)->default('');
            $table->string('customers_company', 64)->nullable();
            $table->string('customers_street_address', 64)->default('');
            $table->string('customers_suburb', 32)->nullable();
            $table->string('customers_city', 32)->default('');
            $table->string('customers_postcode', 10)->default('');
            $table->string('customers_state', 32)->nullable();
            $table->string('customers_country', 64)->default('');
            $table->string('customers_telephone', 32)->default('');
            $table->string('customers_email_address', 96)->default('');
            $table->integer('customers_address_format_id')->default(0);
            $table->string('delivery_name', 64)->default('');
            $table->string('delivery_company', 64)->nullable();
            $table->string('delivery_street_address', 64)->default('');
            $table->string('delivery_suburb', 32)->nullable();
            $table->string('delivery_city', 32)->default('');
            $table->string('delivery_postcode', 10)->default('');
            $table->string('delivery_state', 32)->nullable();
            $table->string('delivery_country', 64)->default('');
            $table->integer('delivery_address_format_id')->default(0);
            $table->string('billing_name', 64)->default('');
            $table->string('billing_company', 64)->nullable();
            $table->string('billing_street_address', 64)->default('');
            $table->string('billing_suburb', 32)->nullable();
            $table->string('billing_city', 32)->default('');
            $table->string('billing_postcode', 10)->default('');
            $table->string('billing_state', 32)->nullable();
            $table->string('billing_country', 64)->default('');
            $table->integer('billing_address_format_id')->default(0);
            $table->string('payment_method', 128)->default('');
            $table->string('payment_module_code', 32)->default('');
            $table->string('shipping_method')->nullable();
            $table->string('shipping_module_code', 32)->default('');
            $table->string('coupon_code', 32)->default('');
            $table->string('cc_type', 20)->nullable();
            $table->string('cc_owner', 64)->nullable();
            $table->string('cc_number', 32)->nullable();
            $table->string('cc_expires', 4)->nullable();
            $table->binary('cc_cvv');
            $table->dateTime('last_modified')->nullable();
            $table->dateTime('date_purchased')->nullable()->index('idx_date_purchased_zen');
            $table->integer('orders_status')->default(0);
            $table->dateTime('orders_date_finished')->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('currency_value', 14, 6)->nullable();
            $table->decimal('order_total', 15, 4)->nullable();
            $table->decimal('order_tax', 15, 4)->nullable();
            $table->integer('paypal_ipn_id')->default(0);
            $table->string('ip_address', 96)->default('');
            $table->float('order_weight')->nullable();
            $table->char('language_code', 2)->default('');

            $table->index(['orders_status', 'orders_id', 'customers_id'], 'idx_status_orders_cust_zen');
            $table->index(['customers_id', 'orders_id'], 'idx_cust_id_orders_id_zen');
            $table->index(['orders_status', 'date_purchased', 'orders_id'], 'idx_status_date_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
