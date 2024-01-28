<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('reviews_id');
            $table->integer('products_id')->default(0)->index('idx_products_id_zen');
            $table->integer('customers_id')->nullable()->index('idx_customers_id_zen');
            $table->string('customers_name', 64)->default('');
            $table->integer('reviews_rating')->nullable();
            $table->dateTime('date_added')->nullable()->index('idx_date_added_zen');
            $table->dateTime('last_modified')->nullable();
            $table->integer('reviews_read')->default(0);
            $table->integer('status')->default(1)->index('idx_status_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
