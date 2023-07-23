<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('currencies_id');
            $table->string('title', 32)->default('');
            $table->char('code', 3)->default('');
            $table->string('symbol_left', 32)->nullable();
            $table->string('symbol_right', 32)->nullable();
            $table->char('decimal_point', 1)->nullable();
            $table->char('thousands_point', 1)->nullable();
            $table->char('decimal_places', 1)->nullable();
            $table->decimal('value', 14, 6)->nullable();
            $table->dateTime('last_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
