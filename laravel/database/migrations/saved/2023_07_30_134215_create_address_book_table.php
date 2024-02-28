<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_book', function (Blueprint $table) {
            $table->increments('address_book_id');
            $table->integer('customers_id')->default(0)->index('idx_address_book_customers_id_zen');
            $table->char('entry_gender', 1)->default('');
            $table->string('entry_company', 64)->nullable();
            $table->string('entry_firstname', 32)->default('');
            $table->string('entry_lastname', 32)->default('');
            $table->string('entry_street_address', 64)->default('');
            $table->string('entry_suburb', 32)->nullable();
            $table->string('entry_postcode', 10)->default('');
            $table->string('entry_city', 32)->default('');
            $table->string('entry_state', 32)->nullable();
            $table->integer('entry_country_id')->default(0);
            $table->integer('entry_zone_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_book');
    }
}
