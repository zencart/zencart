<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMusicGenreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('music_genre', function (Blueprint $table) {
            $table->increments('music_genre_id');
            $table->string('music_genre_name', 32)->default('')->index('idx_music_genre_name_zen');
            $table->dateTime('date_added')->nullable();
            $table->dateTime('last_modified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('music_genre');
    }
}
