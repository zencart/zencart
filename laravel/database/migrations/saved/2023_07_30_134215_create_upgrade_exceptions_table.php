<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUpgradeExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upgrade_exceptions', function (Blueprint $table) {
            $table->increments('upgrade_exception_id');
            $table->string('sql_file', 128)->nullable();
            $table->text('reason')->nullable();
            $table->dateTime('errordate')->nullable();
            $table->text('sqlstatement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upgrade_exceptions');
    }
}
