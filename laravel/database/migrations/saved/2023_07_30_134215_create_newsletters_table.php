<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->increments('newsletters_id');
            $table->string('title')->default('');
            $table->text('content');
            $table->text('content_html');
            $table->string('module')->default('');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->dateTime('date_sent')->nullable();
            $table->integer('status')->nullable();
            $table->integer('locked')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletters');
    }
}
