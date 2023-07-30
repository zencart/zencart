<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_manager', function (Blueprint $table) {
            $table->increments('media_id');
            $table->string('media_name')->default('');
            $table->dateTime('last_modified')->default('0001-01-01 00:00:00');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');

            $table->index([\Illuminate\Support\Facades\DB::raw('media_name(191)')], 'idx_media_name_zen');
            //$table->index(['media_name`(191'], 'idx_media_name_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_manager');
    }
}
