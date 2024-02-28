<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('coupon_id');
            $table->char('coupon_type', 1)->default('F')->index('idx_coupon_type_zen');
            $table->string('coupon_code', 32)->default('')->index('idx_coupon_code_zen');
            $table->decimal('coupon_amount', 15, 4)->default(0.0000);
            $table->decimal('coupon_minimum_order', 15, 4)->default(0.0000);
            $table->dateTime('coupon_start_date')->default('0001-01-01 00:00:00');
            $table->dateTime('coupon_expire_date')->default('0001-01-01 00:00:00');
            $table->integer('uses_per_coupon')->default(1);
            $table->integer('uses_per_user')->default(0);
            $table->string('restrict_to_products')->nullable();
            $table->string('restrict_to_categories')->nullable();
            $table->text('restrict_to_customers')->nullable();
            $table->char('coupon_active', 1)->default('Y');
            $table->dateTime('date_created')->default('0001-01-01 00:00:00');
            $table->dateTime('date_modified')->default('0001-01-01 00:00:00');
            $table->integer('coupon_zone_restriction')->default(0);
            $table->boolean('coupon_calc_base')->default(0);
            $table->integer('coupon_order_limit')->default(0);
            $table->boolean('coupon_is_valid_for_sales')->default(1);
            $table->boolean('coupon_product_count')->default(0);

            $table->index(['coupon_active', 'coupon_type'], 'idx_active_type_zen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
