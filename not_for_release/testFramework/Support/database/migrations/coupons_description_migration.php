<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('coupons_description', function (Blueprint $table) {
            $table->integer('coupon_id')->default(0);
            $table->integer('language_id')->default(0);
            $table->string('coupon_name', 64)->default('');
            $table->text('coupon_description')->nullable();

            $table->primary(['coupon_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('coupons_description');
    }
}
