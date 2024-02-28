<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountProductViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('count_product_views', function (Blueprint $table) {
            $table->integer('product_id')->default(0);
            $table->integer('language_id')->default(1);
            $table->date('date_viewed');
            $table->integer('views')->nullable();

            $table->primary(['product_id', 'language_id', 'date_viewed']);
            $table->index(['language_id', 'product_id', 'date_viewed'], 'idx_pid_lang_date_zen');
            $table->index(['date_viewed', 'product_id', 'language_id'], 'idx_date_pid_lang_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('count_product_views');
    }
}
