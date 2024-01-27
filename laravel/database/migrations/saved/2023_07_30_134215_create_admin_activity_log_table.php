<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_activity_log', function (Blueprint $table) {
            $table->increments('log_id');
            $table->dateTime('access_date')->default('0001-01-01 00:00:00')->index('idx_access_date_zen');
            $table->integer('admin_id')->default(0);
            $table->string('page_accessed', 80)->default('')->index('idx_page_accessed_zen');
            $table->text('page_parameters')->nullable();
            $table->string('ip_address', 45)->default('')->index('idx_ip_zen');
            $table->boolean('flagged')->default(0)->index('idx_flagged_zen');
            $table->mediumText('attention')->nullable();
            $table->binary('gzpost');
            $table->mediumText('logmessage');
            $table->string('severity', 9)->default('info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_activity_log');
    }
}
