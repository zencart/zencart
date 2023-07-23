<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_pages', function (Blueprint $table) {
            $table->string('page_key', 191)->default('')->unique('page_key');
            $table->string('language_key')->default('');
            $table->string('main_page')->default('');
            $table->string('page_params')->default('');
            $table->string('menu_key', 191)->default('');
            $table->char('display_on_menu', 1)->default('N');
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_pages');
    }
}
