<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBannersHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners_history', function (Blueprint $table) {
            $table->increments('banners_history_id');
            $table->integer('banners_id')->default(0)->index('idx_banners_id_zen');
            $table->integer('banners_shown')->default(0);
            $table->integer('banners_clicked')->default(0);
            $table->dateTime('banners_history_date')->default('0001-01-01 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners_history');
    }
}
