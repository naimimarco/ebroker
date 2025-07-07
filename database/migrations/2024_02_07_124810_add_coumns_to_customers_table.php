<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'city')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('city')->nullable();
                });
            }
            if (!Schema::hasColumn('customers', 'state')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('state')->nullable();
                });
            }
            if (!Schema::hasColumn('customers', 'country')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('country')->nullable();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
