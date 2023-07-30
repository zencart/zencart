<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecordArtistsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_artists_info', function (Blueprint $table) {
            $table->integer('artists_id')->default(0);
            $table->integer('languages_id')->default(0);
            $table->string('artists_url')->default('');
            $table->integer('url_clicked')->default(0);
            $table->dateTime('date_last_click')->nullable();

            $table->primary(['artists_id', 'languages_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('record_artists_info');
    }
}
