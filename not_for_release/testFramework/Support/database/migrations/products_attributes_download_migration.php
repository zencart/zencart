<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsAttributesDownloadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('products_attributes_download', function (Blueprint $table) {
            $table->integer('products_attributes_id')->default(0)->primary();
            $table->string('products_attributes_filename')->default('');
            $table->integer('products_attributes_maxdays')->default(0);
            $table->integer('products_attributes_maxcount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('products_attributes_download');
    }
}
