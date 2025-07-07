<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('advertisements',function(Blueprint $table){
            $table->integer('status')->comment('0=approved,1=pending,2=rejected,3=expired')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements',function(Blueprint $table){
            $table->integer('status')->comment('0=approved,1=pending,2=rejected')->change();
        });
    }
};
