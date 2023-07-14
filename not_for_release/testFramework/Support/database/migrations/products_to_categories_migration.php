<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('products_to_categories', function (Blueprint $table) {
            $table->integer('products_id')->default(0);
            $table->integer('categories_id')->default(0);

            $table->primary(['products_id', 'categories_id']);
            $table->index(['categories_id', 'products_id'], 'idx_cat_prod_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('products_to_categories');
    }
}
