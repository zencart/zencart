<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductTypesToCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_types_to_category', function (Blueprint $table) {
            $table->integer('product_type_id')->default(0)->index('idx_product_type_id_zen');
            $table->integer('category_id')->default(0)->index('idx_category_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_types_to_category');
    }
}
