<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_version', function (Blueprint $table) {
            $table->increments('project_version_id');
            $table->string('project_version_key', 40)->default('')->unique('idx_project_version_key_zen');
            $table->string('project_version_major', 20)->default('');
            $table->string('project_version_minor', 20)->default('');
            $table->string('project_version_patch1', 20)->default('');
            $table->string('project_version_patch2', 20)->default('');
            $table->string('project_version_patch1_source', 20)->default('');
            $table->string('project_version_patch2_source', 20)->default('');
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
        Schema::dropIfExists('project_version');
    }
}
