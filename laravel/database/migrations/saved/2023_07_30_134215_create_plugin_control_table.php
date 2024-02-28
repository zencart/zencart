<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_control', function (Blueprint $table) {
            $table->string('unique_key', 40)->primary();
            $table->string('name', 64)->default('');
            $table->text('description')->nullable();
            $table->string('type', 11)->default('free');
            $table->boolean('managed')->default(0);
            $table->boolean('status')->default(0);
            $table->string('author', 64);
            $table->string('version', 10)->nullable();
            $table->text('zc_versions');
            $table->integer('zc_contrib_id')->nullable();
            $table->boolean('infs')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plugin_control');
    }
}
