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
        Schema::create('user_purchased_packages', function (Blueprint $table) {
            $table->id();
            $table->morphs('modal');
            $table->integer('package_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('used_limit_for_property')->default(0);
            $table->integer('used_limit_for_advertisement')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_purchased_packages');
    }
};
