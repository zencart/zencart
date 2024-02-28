<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaxRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->increments('tax_rates_id');
            $table->integer('tax_zone_id')->default(0)->index('idx_tax_zone_id_zen');
            $table->integer('tax_class_id')->default(0)->index('idx_tax_class_id_zen');
            $table->integer('tax_priority')->default(1);
            $table->decimal('tax_rate', 7, 4)->default(0.0000);
            $table->string('tax_description')->default('');
            $table->dateTime('last_modified')->nullable();
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
        Schema::dropIfExists('tax_rates');
    }
}
