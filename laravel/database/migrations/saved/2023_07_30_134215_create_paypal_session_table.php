<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaypalSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_session', function (Blueprint $table) {
            $table->increments('unique_id');
            $table->text('session_id');
            $table->binary('saved_session');
            $table->integer('expiry')->default(0);

            $table->index([\Illuminate\Support\Facades\DB::raw('session_id(36)')], 'idx_session_id_zen');
            //$table->index(['session_id`(36'], 'idx_session_id_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_session');
    }
}
