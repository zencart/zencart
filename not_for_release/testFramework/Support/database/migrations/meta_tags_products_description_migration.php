<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMetaTagsProductsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('meta_tags_products_description', function (Blueprint $table) {
            $table->integer('products_id');
            $table->integer('language_id')->default(1);
            $table->string('metatags_title')->default('');
            $table->text('metatags_keywords')->nullable();
            $table->text('metatags_description')->nullable();

            $table->primary(['products_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('meta_tags_products_description');
    }
}
