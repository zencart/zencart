<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReviewsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews_description', function (Blueprint $table) {
            $table->integer('reviews_id')->default(0);
            $table->integer('languages_id')->default(0);
            $table->text('reviews_text');

            $table->primary(['reviews_id', 'languages_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews_description');
    }
}
