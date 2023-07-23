<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('customers_id');
            $table->char('customers_gender', 1)->default('');
            $table->string('customers_firstname', 32)->default('');
            $table->string('customers_lastname', 32)->default('');
            $table->dateTime('customers_dob')->default('0001-01-01 00:00:00');
            $table->string('customers_email_address', 96)->default('')->index('idx_email_address_zen');
            $table->string('customers_nick', 96)->default('')->index('idx_nick_zen');
            $table->integer('customers_default_address_id')->default(0);
            $table->string('customers_telephone', 32)->default('');
            $table->string('customers_fax', 32)->nullable();
            $table->string('customers_password')->default('');
            $table->string('customers_secret', 64)->default('');
            $table->char('customers_newsletter', 1)->nullable()->index('idx_newsletter_zen');
            $table->integer('customers_group_pricing')->default(0)->index('idx_grp_pricing_zen');
            $table->string('customers_email_format', 4)->default('TEXT');
            $table->integer('customers_authorization')->default(0);
            $table->string('customers_referral', 32)->default('');
            $table->string('registration_ip', 45)->default('');
            $table->string('last_login_ip', 45)->default('');
            $table->string('customers_paypal_payerid', 20)->default('');
            $table->unsignedTinyInteger('customers_paypal_ec')->default(0);

            $table->index([\Illuminate\Support\Facades\DB::raw('customers_referral(10)')], 'idx_referral_zen');
            //$table->index(['customers_referral`(10'], 'idx_referral_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
