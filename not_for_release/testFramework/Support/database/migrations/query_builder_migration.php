<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQueryBuilderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('query_builder', function (Blueprint $table) {
            $table->increments('query_id');
            $table->string('query_category', 40)->default('');
            $table->string('query_name', 80)->default('')->unique('query_name');
            $table->text('query_description');
            $table->text('query_string');
            $table->text('query_keys_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('query_builder');
    }
}
