<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('media_to_products', function (Blueprint $table) {
            $table->integer('media_id')->default(0);
            $table->integer('product_id')->default(0);

            $table->index(['media_id', 'product_id'], 'idx_media_product_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('media_to_products');
    }
}
