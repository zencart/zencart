<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecordCompanyInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_company_info', function (Blueprint $table) {
            $table->integer('record_company_id')->default(0);
            $table->integer('languages_id')->default(0);
            $table->string('record_company_url')->default('');
            $table->integer('url_clicked')->default(0);
            $table->dateTime('date_last_click')->nullable();

            $table->primary(['record_company_id', 'languages_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('record_company_info');
    }
}
