<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMetaTagsCategoriesDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_tags_categories_description', function (Blueprint $table) {
            $table->integer('categories_id');
            $table->integer('language_id')->default(1);
            $table->string('metatags_title')->default('');
            $table->text('metatags_keywords')->nullable();
            $table->text('metatags_description')->nullable();

            $table->primary(['categories_id', 'language_id'], 'idx_meta_tags_categories_description_categories_id_language_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meta_tags_categories_description');
    }
}
