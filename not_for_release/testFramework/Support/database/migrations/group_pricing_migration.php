<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGroupPricingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('group_pricing', function (Blueprint $table) {
            $table->increments('group_id');
            $table->string('group_name', 32)->default('');
            $table->decimal('group_percentage', 5, 2)->default(0.00);
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
        Capsule::schema()->dropIfExists('group_pricing');
    }
}
