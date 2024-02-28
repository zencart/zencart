<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTotalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_total', function (Blueprint $table) {
            $table->increments('orders_total_id');
            $table->integer('orders_id')->default(0)->index('idx_ot_orders_id_zen');
            $table->string('title')->default('');
            $table->string('text')->default('');
            $table->decimal('value', 15, 4)->default(0.0000);
            $table->string('class', 32)->default('')->index('idx_ot_class_zen');
            $table->integer('sort_order')->default(0);

            $table->index(['orders_id', 'class'], 'idx_oid_class_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_total');
    }
}
