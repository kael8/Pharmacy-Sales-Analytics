<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModProductsAndSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 8, 2)->after('price'); // Add cost price of the product
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('revenue', 10, 2)->after('quantity_sold'); // Add revenue per sale
            $table->decimal('cost', 10, 2)->after('revenue'); // Add cost of goods sold per sale
            $table->decimal('profit', 10, 2)->after('cost'); // Add profit per sale
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
