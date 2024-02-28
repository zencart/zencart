<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectVersionHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_version_history', function (Blueprint $table) {
            $table->increments('project_version_id');
            $table->string('project_version_key', 40)->default('');
            $table->string('project_version_major', 20)->default('');
            $table->string('project_version_minor', 20)->default('');
            $table->string('project_version_patch', 20)->default('');
            $table->string('project_version_comment', 250)->default('');
            $table->dateTime('project_version_date_applied')->default('0001-01-01 01:01:01');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_version_history');
    }
}
