<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponRestrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('coupon_restrict', function (Blueprint $table) {
            $table->increments('restrict_id');
            $table->integer('coupon_id')->default(0);
            $table->integer('product_id')->default(0);
            $table->integer('category_id')->default(0);
            $table->char('coupon_restrict', 1)->default('N');

            $table->index(['coupon_id', 'product_id'], 'idx_coup_id_prod_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('coupon_restrict');
    }
}
