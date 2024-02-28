<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSalemakerSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salemaker_sales', function (Blueprint $table) {
            $table->increments('sale_id');
            $table->boolean('sale_status')->default(0)->index('idx_sale_status_zen');
            $table->string('sale_name', 128)->default('');
            $table->decimal('sale_deduction_value', 15, 4)->default(0.0000);
            $table->boolean('sale_deduction_type')->default(0);
            $table->decimal('sale_pricerange_from', 15, 4)->default(0.0000);
            $table->decimal('sale_pricerange_to', 15, 4)->default(0.0000);
            $table->boolean('sale_specials_condition')->default(0);
            $table->text('sale_categories_selected')->nullable();
            $table->text('sale_categories_all')->nullable();
            $table->date('sale_date_start')->default('0001-01-01')->index('idx_sale_date_start_zen');
            $table->date('sale_date_end')->default('0001-01-01')->index('idx_sale_date_end_zen');
            $table->date('sale_date_added')->default('0001-01-01');
            $table->date('sale_date_last_modified')->default('0001-01-01');
            $table->date('sale_date_status_change')->default('0001-01-01');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salemaker_sales');
    }
}
