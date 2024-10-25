<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Ensure batch_id is varchar(255) to match inventories table
            $table->string('batch_id', 30)->after('product_id'); // Ensure the length matches that in inventories


            // Ensure revenue, cost, profit, total_amount remain as decimals if needed
            $table->decimal('revenue', 10, 2)->nullable()->change();
            $table->decimal('cost', 10, 2)->change();
            $table->decimal('profit', 10, 2)->change();
            $table->decimal('total_amount', 10, 2)->change();

            // Add foreign key constraint for batch_id
            $table->foreign('batch_id')->references('batch_id')->on('inventories')->onDelete('cascade');

            // Add indexes for better performance
            $table->index(['batch_id']);
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the column
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');

            // Revert revenue, cost, profit, total_amount to their original types
            $table->float('revenue')->nullable()->change();
            $table->float('cost')->change();
            $table->float('profit')->change();
            $table->float('total_amount')->change();
        });
    }
}
