<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDbCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_cache', function (Blueprint $table) {
            $table->string('cache_entry_name', 64)->default('')->primary();
            $table->binary('cache_data');
            $table->integer('cache_entry_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_cache');
    }
}
