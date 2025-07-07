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
        if (!Schema::hasColumn('customers', 'about_me')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->text('about_me')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'facebook_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('facebook_id')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'twiiter_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('twiiter_id')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'instagram_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('instagram_id')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'pintrest_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('pintrest_id')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'latitude')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('latitude')->nullable();
            });
        }
        if (!Schema::hasColumn('customers', 'longitude')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('longitude')->nullable();
            });
        }
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
