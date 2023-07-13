<?php

namespace Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemplateSelectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('template_select', function (Blueprint $table) {
            $table->increments('template_id');
            $table->string('template_dir', 64)->default('');
            $table->string('template_language', 64)->default('0')->index('idx_tpl_lang_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('template_select');
    }
}
