<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('countries_id');
            $table->string('countries_name', 64)->default('')->index('idx_countries_name_zen');
            $table->char('countries_iso_code_2', 2)->default('')->index('idx_iso_2_zen');
            $table->char('countries_iso_code_3', 3)->default('')->index('idx_iso_3_zen');
            $table->integer('address_format_id')->default(0)->index('idx_address_format_id_zen');
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
