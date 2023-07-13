<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('categories_description', function (Blueprint $table) {
            $table->integer('categories_id')->default(0);
            $table->integer('language_id')->default(1);
            $table->string('categories_name', 32)->default('')->index('idx_categories_name_zen');
            $table->text('categories_description');

            $table->primary(['categories_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('categories_description');
    }
}
