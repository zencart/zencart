<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductMusicExtraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_music_extra', function (Blueprint $table) {
            $table->integer('products_id')->default(0)->primary();
            $table->integer('artists_id')->default(0)->index('idx_artists_id_zen');
            $table->integer('record_company_id')->default(0)->index('idx_record_company_id_zen');
            $table->integer('music_genre_id')->default(0)->index('idx_music_genre_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_music_extra');
    }
}
