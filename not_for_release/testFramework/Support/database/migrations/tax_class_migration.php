<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaxClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('tax_class', function (Blueprint $table) {
            $table->increments('tax_class_id');
            $table->string('tax_class_title', 32)->default('');
            $table->string('tax_class_description')->default('');
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
        Capsule::schema()->dropIfExists('tax_class');
    }
}
