<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_description', function (Blueprint $table) {
            $table->integer('products_id');
            $table->integer('language_id')->default(1);
            $table->string('products_name', 191)->default('')->index('idx_products_name_zen');
            $table->text('products_description')->nullable();
            $table->string('products_url')->nullable();
            $table->integer('products_viewed')->default(0);

            $table->primary(['products_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_description');
    }
}
