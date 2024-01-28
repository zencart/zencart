<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_options', function (Blueprint $table) {
            $table->integer('products_options_id')->default(0);
            $table->integer('language_id')->default(1)->index('idx_lang_id_zen');
            $table->string('products_options_name', 32)->default('')->index('idx_products_options_name_zen');
            $table->integer('products_options_sort_order')->default(0)->index('idx_products_options_sort_order_zen');
            $table->integer('products_options_type')->default(0);
            $table->smallInteger('products_options_length')->default(32);
            $table->string('products_options_comment', 256)->nullable();
            $table->smallInteger('products_options_comment_position')->default(0);
            $table->smallInteger('products_options_size')->default(32);
            $table->integer('products_options_images_per_row')->default(5);
            $table->integer('products_options_images_style')->default(0);
            $table->smallInteger('products_options_rows')->default(1);

            $table->primary(['products_options_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_options');
    }
}
