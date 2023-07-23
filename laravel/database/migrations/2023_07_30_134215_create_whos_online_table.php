<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWhosOnlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whos_online', function (Blueprint $table) {
            $table->integer('customer_id')->nullable()->index('idx_customer_id_zen');
            $table->string('full_name', 64)->default('');
            $table->string('session_id', 191)->default('')->index('idx_session_id_zen');
            $table->string('ip_address', 45)->default('')->index('idx_ip_address_zen');
            $table->string('time_entry', 14)->default('')->index('idx_time_entry_zen');
            $table->string('time_last_click', 14)->default('')->index('idx_time_last_click_zen');
            $table->string('last_page_url')->default('');
            $table->text('host_address');
            $table->string('user_agent')->default('');

            $table->index([\Illuminate\Support\Facades\DB::raw('last_page_url(191)')], 'idx_last_page_url_zen');
            //$table->index(['last_page_url`(191'], 'idx_last_page_url_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whos_online');
    }
}
