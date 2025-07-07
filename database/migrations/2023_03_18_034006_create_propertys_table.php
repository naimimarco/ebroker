<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::create('propertys', function (Blueprint $table) {
            $table->id();
            $table->string('category_id');
            $table->integer('package_id')->nullable();
            $table->string('title');
            $table->longText('description');
            $table->string('address');
            $table->string('client_address');
            $table->tinyInteger('propery_type')->comment('0:Sell 1:Rent');
            $table->string('price');
            $table->string('post_type')->nullable()->comment('0 :admin 1:customer');
            $table->string('city')->default('Kutch')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->text('title_image');
            $table->string('threeD_image');
            $table->string('video_link');
            $table->double('latitude');
            $table->double('longitude');
            $table->tinyInteger('added_by')->default(0);
            $table->tinyInteger('status')->default(0)->comment(' 0: Deactive 1: Active');
            $table->bigInteger('total_click')->default(0);
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('propertys');
        Schema::enableForeignKeyConstraints();
    }
};
