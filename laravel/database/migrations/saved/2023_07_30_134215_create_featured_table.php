<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeaturedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured', function (Blueprint $table) {
            $table->increments('featured_id');
            $table->integer('products_id')->default(0)->index('idx_products_id_zen');
            $table->dateTime('featured_date_added')->nullable();
            $table->dateTime('featured_last_modified')->nullable();
            $table->date('expires_date')->default('0001-01-01')->index('idx_expires_date_zen');
            $table->dateTime('date_status_change')->nullable();
            $table->integer('status')->default(1)->index('idx_status_zen');
            $table->date('featured_date_available')->default('0001-01-01')->index('idx_date_avail_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('featured');
    }
}
