<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLayoutBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layout_boxes', function (Blueprint $table) {
            $table->increments('layout_id');
            $table->string('layout_template', 64)->default('');
            $table->string('layout_box_name', 64)->default('');
            $table->boolean('layout_box_status')->default(0)->index('idx_layout_box_status_zen');
            $table->boolean('layout_box_location')->default(0);
            $table->integer('layout_box_sort_order')->default(0)->index('idx_layout_box_sort_order_zen');
            $table->integer('layout_box_sort_order_single')->default(0);
            $table->boolean('layout_box_status_single')->default(0);
            $table->string('plugin_details', 100)->default('');

            $table->index(['layout_template', 'layout_box_name'], 'idx_name_template_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('layout_boxes');
    }
}
