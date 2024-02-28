<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_info', function (Blueprint $table) {
            $table->integer('customers_info_id')->default(0)->primary();
            $table->dateTime('customers_info_date_of_last_logon')->nullable();
            $table->integer('customers_info_number_of_logons')->nullable();
            $table->dateTime('customers_info_date_account_created')->nullable();
            $table->dateTime('customers_info_date_account_last_modified')->nullable();
            $table->integer('global_product_notifications')->default(0);

            $table->index(['customers_info_date_account_created', 'customers_info_id'], 'idx_date_created_cust_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_info');
    }
}
