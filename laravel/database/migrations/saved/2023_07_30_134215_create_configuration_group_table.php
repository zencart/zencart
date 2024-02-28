<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConfigurationGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration_group', function (Blueprint $table) {
            $table->increments('configuration_group_id');
            $table->string('configuration_group_title', 64)->default('');
            $table->string('configuration_group_description')->default('');
            $table->integer('sort_order')->nullable();
            $table->integer('visible')->default(1)->index('idx_visible_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuration_group');
    }
}
