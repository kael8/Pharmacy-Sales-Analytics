<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToInventoriesBatchId extends Migration
{
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Ensure batch_id is a string with a suitable length
            $table->string('batch_id', 30)->change(); // Change the length as needed
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Revert to previous definition if necessary
            $table->string('batch_id', 255)->change(); // Adjust based on original definition
        });
    }
}
