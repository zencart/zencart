<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('categories_id');
            $table->string('categories_image')->nullable();
            $table->integer('parent_id')->default(0);
            $table->integer('sort_order')->nullable()->index('idx_sort_order_zen');
            $table->dateTime('date_added')->nullable();
            $table->dateTime('last_modified')->nullable();
            $table->boolean('categories_status')->default(1)->index('idx_status_zen');

            $table->index(['parent_id', 'categories_id'], 'idx_parent_id_cat_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
