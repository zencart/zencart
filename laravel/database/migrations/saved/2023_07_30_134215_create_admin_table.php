<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string('admin_name', 32)->default('')->index('idx_admin_name_zen');
            $table->string('admin_email', 96)->default('')->index('idx_admin_email_zen');
            $table->integer('admin_profile')->default(0)->index('idx_admin_profile_zen');
            $table->string('admin_pass')->default('');
            $table->string('prev_pass1')->default('');
            $table->string('prev_pass2')->default('');
            $table->string('prev_pass3')->default('');
            $table->dateTime('pwd_last_change_date')->default('0001-01-01 00:00:00');
            $table->string('reset_token')->default('');
            $table->dateTime('last_modified')->default('0001-01-01 00:00:00');
            $table->dateTime('last_login_date')->default('0001-01-01 00:00:00');
            $table->string('last_login_ip', 45)->default('');
            $table->unsignedSmallInteger('failed_logins')->default(0);
            $table->integer('lockout_expires')->default(0);
            $table->dateTime('last_failed_attempt')->default('0001-01-01 00:00:00');
            $table->string('last_failed_ip', 45)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin');
    }
}
