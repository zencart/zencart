<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZonesToGeoZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones_to_geo_zones', function (Blueprint $table) {
            $table->increments('association_id');
            $table->integer('zone_country_id')->default(0);
            $table->integer('zone_id')->nullable();
            $table->integer('geo_zone_id')->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');

            $table->index(['geo_zone_id', 'zone_country_id', 'zone_id'], 'idx_zones_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zones_to_geo_zones');
    }
}
