<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEzpagesContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ezpages_content', function (Blueprint $table) {
            $table->integer('pages_id')->default(0);
            $table->integer('languages_id')->default(1)->index('idx_lang_id_zen');
            $table->string('pages_title', 64)->default('');
            $table->mediumText('pages_html_text')->nullable();

            $table->unique(['pages_id', 'languages_id'], 'idx_ezpages_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ezpages_content');
    }
}
