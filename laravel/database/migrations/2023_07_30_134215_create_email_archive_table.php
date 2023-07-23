<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_archive', function (Blueprint $table) {
            $table->increments('archive_id');
            $table->string('email_to_name', 96)->default('');
            $table->string('email_to_address', 96)->default('')->index('idx_email_to_address_zen');
            $table->string('email_from_name', 96)->default('');
            $table->string('email_from_address', 96)->default('');
            $table->string('email_subject')->default('');
            $table->text('email_html');
            $table->text('email_text');
            $table->dateTime('date_sent')->default('0001-01-01 00:00:00');
            $table->string('module', 64)->default('')->index('idx_module_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_archive');
    }
}
