<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthorizenetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorizenet', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->default(0);
            $table->integer('order_id')->default(0);
            $table->integer('response_code')->default(0);
            $table->string('response_text')->default('');
            $table->string('authorization_type', 50)->default('');
            $table->string('transaction_id', 32)->nullable();
            $table->longText('sent');
            $table->longText('received');
            $table->string('time', 50)->default('');
            $table->string('session_id')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authorizenet');
    }
}
