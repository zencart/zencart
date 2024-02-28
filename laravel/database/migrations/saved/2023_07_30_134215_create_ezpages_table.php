<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEzpagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ezpages', function (Blueprint $table) {
            $table->increments('pages_id');
            $table->string('alt_url')->default('');
            $table->string('alt_url_external')->default('');
            $table->integer('status_header')->default(1)->index('idx_ezp_status_header_zen');
            $table->integer('status_sidebox')->default(1)->index('idx_ezp_status_sidebox_zen');
            $table->integer('status_footer')->default(1)->index('idx_ezp_status_footer_zen');
            $table->integer('status_visible')->default(0);
            $table->integer('status_toc')->default(1)->index('idx_ezp_status_toc_zen');
            $table->integer('header_sort_order')->default(0);
            $table->integer('sidebox_sort_order')->default(0);
            $table->integer('footer_sort_order')->default(0);
            $table->integer('toc_sort_order')->default(0);
            $table->integer('page_open_new_window')->default(0);
            $table->integer('page_is_ssl')->default(0);
            $table->integer('toc_chapter')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ezpages');
    }
}
