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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('type')->comment('Slider,HomeScreen,ProductListing');
            $table->integer('slider_id')->nullable();
            $table->string('title')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('image');
            $table->integer('property_id')->nullable();
            $table->integer('package_id')->nullable();
            $table->boolean('is_enable');
            $table->integer('status')->comment('0=approved,1=pending,2=rejected');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
};
