<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaClipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_clips', function (Blueprint $table) {
            $table->increments('clip_id');
            $table->integer('media_id')->default(0)->index('idx_media_id_zen');
            $table->smallInteger('clip_type')->default(0)->index('idx_clip_type_zen');
            $table->text('clip_filename');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->dateTime('last_modified')->default('0001-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_clips');
    }
}
