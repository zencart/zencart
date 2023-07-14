<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('zones', function (Blueprint $table) {
            $table->increments('zone_id');
            $table->integer('zone_country_id')->default(0)->index('idx_zone_country_id_zen');
            $table->string('zone_code', 32)->default('')->index('idx_zone_code_zen');
            $table->string('zone_name', 32)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('zones');
    }
}
