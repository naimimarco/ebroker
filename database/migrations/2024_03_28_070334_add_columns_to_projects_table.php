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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'slug_id')) {
                Schema::table('projects', function (Blueprint $table) {
                    $table->text('slug_id');
                });
            }
            if (!Schema::hasColumn('projects', 'category_id')) {
                Schema::table('projects', function (Blueprint $table) {
                    $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
                });
            }
            if (!Schema::hasColumn('projects', 'status')) {
                Schema::table('projects', function (Blueprint $table) {
                    $table->boolean('status')->default(0);
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
        Schema::table('projects', function (Blueprint $table) {
            //
        });
    }
};
