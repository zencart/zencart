<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('banners_id');
            $table->string('banners_title', 64)->default('');
            $table->string('banners_url')->default('');
            $table->string('banners_image')->default('');
            $table->string('banners_group', 15)->default('');
            $table->text('banners_html_text')->nullable();
            $table->integer('expires_impressions')->default(0);
            $table->dateTime('expires_date')->nullable()->index('idx_expires_date_zen');
            $table->dateTime('date_scheduled')->nullable()->index('idx_date_scheduled_zen');
            $table->dateTime('date_added')->default('0001-01-01 00:00:00');
            $table->dateTime('date_status_change')->nullable();
            $table->integer('status')->default(1);
            $table->integer('banners_open_new_windows')->default(1);
            $table->integer('banners_on_ssl')->default(1);
            $table->integer('banners_sort_order')->default(0);

            $table->index(['status', 'banners_group'], 'idx_status_group_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
