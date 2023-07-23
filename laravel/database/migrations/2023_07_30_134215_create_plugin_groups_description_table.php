<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginGroupsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_groups_description', function (Blueprint $table) {
            $table->string('plugin_group_unique_key', 20);
            $table->integer('language_id')->default(1);
            $table->string('name', 64)->default('');

            $table->primary(['plugin_group_unique_key', 'language_id'], 'idx_plugin_group_description_plugin_group_unique_key_language_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plugin_groups_description');
    }
}
